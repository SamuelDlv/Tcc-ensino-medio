<?php
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['ID_USER'])) {
    http_response_code(401);
    exit('Acesso não autorizado.');
}

$id_ocorrencia = (int) ($_GET['id_ocorrencia'] ?? 0);
if ($id_ocorrencia < 1) {
    http_response_code(400);
    exit('Ocorrência inválida.');
}

$query = $con->prepare('SELECT ID_OCORR, titulo, data_ocorr, descricao, ID_BAIA, ID_LAB, status_ocorrencia FROM Ocorrencias WHERE ID_OCORR = ? LIMIT 1');
$query->bind_param('i', $id_ocorrencia);
$query->execute();
$ocorrencia = $query->get_result()->fetch_assoc();
$query->close();

if (!$ocorrencia) {
    http_response_code(404);
    exit('Ocorrência não encontrada.');
}

$status = [
    0 => 'Funcionando',
    1 => 'Com defeito',
    2 => 'Em manutenção',
][(int) $ocorrencia['status_ocorrencia']] ?? 'Não informado';

function pdfEncode(string $text): string
{
    $text = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text ?: '');
}

$lines = [
    'BETTER ORGANIZED - RELATORIO DE OCORRENCIA',
    '',
    'ID da ocorrencia: ' . (int) $ocorrencia['ID_OCORR'],
    'Titulo: ' . $ocorrencia['titulo'],
    'Data: ' . $ocorrencia['data_ocorr'],
    'Laboratorio: ' . (int) $ocorrencia['ID_LAB'],
    'Baia: ' . (int) $ocorrencia['ID_BAIA'],
    'Situacao: ' . $status,
    '',
    'Descricao:',
];

$descricao = preg_split('/\s+/u', trim((string) $ocorrencia['descricao'])) ?: [];
$current = '';
foreach ($descricao as $word) {
    if (strlen($current . ' ' . $word) > 88) {
        $lines[] = $current;
        $current = $word;
    } else {
        $current = trim($current . ' ' . $word);
    }
}
if ($current !== '') {
    $lines[] = $current;
}

$content = "BT\n/F1 12 Tf\n50 790 Td\n";
foreach ($lines as $index => $line) {
    if ($index > 0) {
        $content .= "0 -18 Td\n";
    }
    $content .= '(' . pdfEncode($line) . ") Tj\n";
}
$content .= "ET\n";

$objects = [];
$objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
$objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
$objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>';
$objects[] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . 'endstream';
$objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

$pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
$offsets = [0];
foreach ($objects as $number => $object) {
    $objectNumber = $number + 1;
    $offsets[$objectNumber] = strlen($pdf);
    $pdf .= $objectNumber . " 0 obj\n" . $object . "\nendobj\n";
}
$xref = strlen($pdf);
$pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
$pdf .= "0000000000 65535 f \n";
for ($i = 1; $i <= count($objects); $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
}
$pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ocorrencia-' . (int) $ocorrencia['ID_OCORR'] . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
