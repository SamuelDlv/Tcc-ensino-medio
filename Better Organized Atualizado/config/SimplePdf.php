<?php
/**
 * SimplePdf — gerador de PDF mínimo, sem nenhuma dependência externa.
 *
 * Não é uma biblioteca completa (não faz Unicode complexo, imagens, etc.),
 * mas gera arquivos .pdf válidos com texto, cores, linhas e paginação
 * automática, o suficiente para os relatórios deste projeto.
 * Usa apenas as fontes padrão do PDF (Helvetica), então não precisa de
 * nenhum arquivo de fonte nem do Composer.
 */
class SimplePdf
{
    private const PAGE_W = 595.28; // A4 em pontos (72dpi)
    private const PAGE_H = 841.89;
    private const MARGIN = 40;

    /** @var array<int, string> conteúdo (comandos de desenho) de cada página */
    private array $pages = [];
    private string $buffer = '';
    private float $x;
    private float $y;
    private string $font = 'F1'; // Helvetica
    private float $fontSize = 11;
    private array $fillColor = [0, 0, 0];

    public function __construct()
    {
        $this->newPage();
    }

    private function newPage(): void
    {
        if ($this->buffer !== '') {
            $this->pages[] = $this->buffer;
        }
        $this->buffer = "";
        $this->x = self::MARGIN;
        $this->y = self::PAGE_H - self::MARGIN;
    }

    public function getY(): float
    {
        return $this->y;
    }

    public function setXY(float $x, float $y): void
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Garante espaço vertical disponível; quebra página se necessário.
     * Retorna true se uma nova página foi criada.
     */
    public function ensureSpace(float $needed): bool
    {
        if ($this->y - $needed < self::MARGIN) {
            $this->newPage();
            return true;
        }
        return false;
    }

    public function setFont(string $font, float $size): void
    {
        $this->font = $font === 'B' ? 'F2' : 'F1';
        $this->fontSize = $size;
    }

    public function setColor(int $r, int $g, int $b): void
    {
        $this->fillColor = [$r / 255, $g / 255, $b / 255];
    }

    private function esc(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        // PDF core fonts (WinAnsi) não têm UTF-8; convertemos para Latin-1
        // (cp1252), que cobre acentuação em português.
        $converted = @iconv('UTF-8', 'CP1252//TRANSLIT', $text);
        return $converted !== false ? $converted : $text;
    }

    /** Escreve uma linha de texto na posição atual e avança Y. */
    public function text(string $text, ?float $x = null, ?float $y = null): void
    {
        $x ??= $this->x;
        $y ??= $this->y;
        [$r, $g, $b] = $this->fillColor;
        $this->buffer .= sprintf(
            "BT /%s %.2F Tf %.3F %.3F %.3F rg %.2F %.2F Td (%s) Tj ET\n",
            $this->font,
            $this->fontSize,
            $r, $g, $b,
            $x,
            $y,
            $this->esc($text)
        );
    }

    /** Retângulo preenchido. */
    public function rect(float $x, float $y, float $w, float $h): void
    {
        [$r, $g, $b] = $this->fillColor;
        $this->buffer .= sprintf("%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f\n", $r, $g, $b, $x, $y, $w, $h);
    }

    /** Linha horizontal simples. */
    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.5): void
    {
        [$r, $g, $b] = $this->fillColor;
        $this->buffer .= sprintf("%.3F %.3F %.3F RG %.2F w %.2F %.2F m %.2F %.2F l S\n", $r, $g, $b, $width, $x1, $y1, $x2, $y2);
    }

    /** Quebra um texto em várias linhas respeitando uma largura máxima (aprox.). */
    public function wrapText(string $text, float $maxWidth): array
    {
        $avgCharWidth = $this->fontSize * 0.52;
        $maxChars = max(1, (int)floor($maxWidth / $avgCharWidth));
        $lines = [];
        foreach (explode("\n", $text) as $paragraph) {
            $wrapped = wordwrap($paragraph === '' ? ' ' : $paragraph, $maxChars, "\n", true);
            foreach (explode("\n", $wrapped) as $line) {
                $lines[] = $line;
            }
        }
        return $lines;
    }

    public function pageWidth(): float
    {
        return self::PAGE_W;
    }

    public function marginX(): float
    {
        return self::MARGIN;
    }

    public function contentWidth(): float
    {
        return self::PAGE_W - (2 * self::MARGIN);
    }

    /** Gera os bytes finais do PDF. */
    public function output(): string
    {
        if ($this->buffer !== '') {
            $this->pages[] = $this->buffer;
            $this->buffer = '';
        }
        if (empty($this->pages)) {
            $this->pages[] = '';
        }

        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

        $pageCount = count($this->pages);
        $pageObjNums = [];
        $firstContentObj = 4 + $pageCount; // depois de catálogo(1), pages(2), fontes(3,4-ish)
        // Numeração: 1=catalog 2=pages 3=font Helvetica 4=font Helvetica-Bold
        // 5..(5+n-1) = páginas, depois n objetos de conteúdo
        $fontRegular = 3;
        $fontBold = 4;
        $pageStart = 5;
        $contentStart = $pageStart + $pageCount;

        $kids = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $pageNum = $pageStart + $i;
            $contentNum = $contentStart + $i;
            $kids[] = "$pageNum 0 R";
            $objects[$pageNum] = "<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 $fontRegular 0 R /F2 $fontBold 0 R >> >> /Contents $contentNum 0 R >>";
            $stream = $this->pages[$i];
            $objects[$contentNum] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
        }

        $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count $pageCount /MediaBox [0 0 " . self::PAGE_W . " " . self::PAGE_H . "] >>";
        $objects[$fontRegular] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objects[$fontBold] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "$num 0 obj\n$body\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxObj = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($n = 1; $n <= $maxObj; $n++) {
            if (isset($offsets[$n])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$n]);
            } else {
                $pdf .= "0000000000 00000 f \n";
            }
        }
        $pdf .= "trailer\n<< /Size " . ($maxObj + 1) . " /Root 1 0 R >>\nstartxref\n$xrefOffset\n%%EOF";

        return $pdf;
    }

    /** Envia o PDF como download direto (Content-Disposition: attachment). */
    public function download(string $filename): void
    {
        $data = $this->output();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($data));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $data;
        exit();
    }
}
