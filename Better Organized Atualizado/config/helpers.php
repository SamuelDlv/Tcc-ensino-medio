<?php
/**
 * Funções compartilhadas entre as telas do painel (professor/suporte).
 * status_ocorrencia / status_maquina: 0 = ok | 1 = com defeito | 2 = manutenção
 *
 * Schema novo (peças/máquinas/laboratórios):
 *   Lab 1---N Maquina 1---1 Pec ---> Memory_ram / Memory / Placa_video / Perifericos
 *   Lab 1---N Baia 1---1 Maquina (uma baia guarda qual máquina está instalada nela)
 *   Ocorrencias/Historico referenciam Lab direto (+ ID_MAQ opcional e tipo_problema)
 */

require_once __DIR__ . '/SimplePdf.php';

/**
 * Garante que o usuário logado tem a função exigida (0 = suporte, 1 = professor).
 * Deve ser chamada logo após checar $_SESSION["ID_USER"], em toda página que
 * só um dos dois perfis deve acessar. Quem não tem a função certa é mandado
 * de volta pra tela inicial do próprio perfil (sem vazar erro, sem permitir a ação).
 */
function requireRole(int $funcaoEsperada): void {
    $funcaoAtual = $_SESSION["FUNCAO"] ?? null;
    if ($funcaoAtual !== $funcaoEsperada) {
        $destino = $funcaoAtual === 1 ? '/professor/inicio.php' : '/suporte/inicio.php';
        header("Location: $destino");
        exit();
    }
}

/** Rótulo, classe CSS e cor de um status_ocorrencia / status_maquina. */
function statusInfo(int $status): array {
    switch ($status) {
        case 1:
            return ['label' => 'Com defeito', 'class' => 'is-error'];
        case 2:
            return ['label' => 'Manutenção', 'class' => 'is-warn'];
        default:
            return ['label' => 'Funcionando', 'class' => 'is-ok'];
    }
}

/** Lista de tipos de problema aceitos no formulário de ocorrência. */
function tiposProblema(): array {
    return ['Hardware', 'Software', 'Rede', 'Outro'];
}

/** Quantidade de máquinas cadastradas em um laboratório (sempre calculada, nunca armazenada). */
function getQuantMaquinas(mysqli $con, int $idLab): int {
    $query = $con->prepare("SELECT COUNT(*) FROM Maquina WHERE ID_LAB = ?");
    $query->bind_param("i", $idLab);
    $query->execute();
    $query->bind_result($total);
    $query->fetch();
    $query->close();
    return (int)$total;
}

/**
 * Pior status entre as máquinas de um laboratório (para o card de seleção
 * de laboratório na tela inicial): prioriza defeito (1) sobre manutenção (2)
 * sobre ok (0). Se o laboratório não tiver nenhuma máquina, considera ok.
 */
function getStatusLaboratorio(mysqli $con, int $id_lab): int {
    $query = $con->prepare("SELECT status_maquina FROM Maquina WHERE ID_LAB = ?");
    $query->bind_param("i", $id_lab);
    $query->execute();
    $result = $query->get_result();

    $pior = 0;
    while ($row = $result->fetch_assoc()) {
        $s = (int)$row['status_maquina'];
        if ($s === 1) {
            $pior = 1;
            break; // defeito é o pior caso possível, não precisa continuar
        }
        if ($s === 2 && $pior === 0) {
            $pior = 2;
        }
    }
    $query->close();

    return $pior;
}

/**
 * Lista as baias de um laboratório com a máquina instalada (se houver),
 * ordenadas pelo número da baia. Cada linha já vem com o status calculado.
 * Só retorna baias que existem de verdade (lista dinâmica, sem mapa fixo).
 */
function listarBaiasComMaquina(mysqli $con, int $idLab): array {
    $query = $con->prepare(
        "SELECT b.ID_BAIA, b.numero, m.ID_MAQ, m.apelido, m.status_maquina
         FROM Baia b
         LEFT JOIN Maquina m ON m.ID_MAQ = b.ID_MAQ
         WHERE b.ID_LAB = ?
         ORDER BY b.numero"
    );
    $query->bind_param("i", $idLab);
    $query->execute();
    $rows = $query->get_result()->fetch_all(MYSQLI_ASSOC);
    $query->close();
    return $rows;
}

/**
 * Lista todas as máquinas de um laboratório (com ou sem baia atribuída),
 * já com o número da baia quando existir. Usada na tela de cadastro/edição
 * de máquinas, que não depende de a máquina já estar numa baia.
 */
function listarMaquinasDoLab(mysqli $con, int $idLab): array {
    $query = $con->prepare(
        "SELECT m.ID_MAQ, m.apelido, m.status_maquina, b.ID_BAIA, b.numero
         FROM Maquina m
         LEFT JOIN Baia b ON b.ID_MAQ = m.ID_MAQ
         WHERE m.ID_LAB = ?
         ORDER BY (b.numero IS NULL), b.numero, m.ID_MAQ"
    );
    $query->bind_param("i", $idLab);
    $query->execute();
    $rows = $query->get_result()->fetch_all(MYSQLI_ASSOC);
    $query->close();
    return $rows;
}

/**
 * Exclui uma máquina e a baia que a referencia (se houver). As peças
 * associadas (Pec/Memory/Memory_ram/Placa_video/Perifericos) ficam órfãs
 * de propósito — não são apagadas — pra não perder o histórico de hardware.
 */
function excluirMaquina(mysqli $con, int $idMaq): void {
    $stmt = $con->prepare("DELETE FROM Baia WHERE ID_MAQ = ?");
    $stmt->bind_param("i", $idMaq);
    $stmt->execute();
    $stmt->close();

    $stmt = $con->prepare("DELETE FROM Maquina WHERE ID_MAQ = ?");
    $stmt->bind_param("i", $idMaq);
    $stmt->execute();
    $stmt->close();
}

/** Busca os dados completos de uma máquina (peças + componentes) pelo ID. */
function buscarMaquinaCompleta(mysqli $con, int $idMaq): ?array {
    $query = $con->prepare(
        "SELECT m.ID_MAQ, m.ID_LAB, m.status_maquina, m.apelido,
                p.ID_PEC_SET, p.processador, p.motherboard, p.gravador_dvd, p.fonte, p.placa_rede,
                mr.MRAM_SET, mr.slot1, mr.slot2, mr.slot3, mr.slot4, mr.modelo AS ram_modelo,
                me.MEMO_SET, me.HD1, me.HD2, me.SSD1, me.SSD2, me.modelo AS storage_modelo,
                pv.PLAC_VID, pv.nome AS placa_nome, pv.modelo AS placa_modelo, pv.marca AS placa_marca, pv.verifica AS placa_verifica,
                pe.PERI_SET, pe.monitor, pe.teclado, pe.estabilizador
         FROM Maquina m
         LEFT JOIN Pec p ON p.ID_PEC_SET = m.ID_PEC_SET
         LEFT JOIN Memory_ram mr ON mr.MRAM_SET = p.MRAM_SET
         LEFT JOIN Memory me ON me.MEMO_SET = p.MEMO_SET
         LEFT JOIN Placa_video pv ON pv.PLAC_VID = p.PLAC_VID
         LEFT JOIN Perifericos pe ON pe.PERI_SET = p.PERI_SET
         WHERE m.ID_MAQ = ?"
    );
    $query->bind_param("i", $idMaq);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $query->close();
    return $row ?: null;
}

/**
 * Cria (ou atualiza, se $idMaq for passado) uma máquina completa: grava os
 * componentes de hardware em suas tabelas, monta o Pec, e a Maquina em si.
 * $dados é um array associativo com todos os campos do formulário.
 * Retorna o ID_MAQ criado/atualizado.
 */
function salvarMaquinaCompleta(mysqli $con, array $dados, ?int $idMaq = null): int {
    // 1. Memory_ram
    $mramStmt = $con->prepare(
        "INSERT INTO Memory_ram (slot1, slot2, slot3, slot4, modelo) VALUES (?, ?, ?, ?, ?)"
    );
    $mramStmt->bind_param(
        "sssss",
        $dados['ram_slot1'], $dados['ram_slot2'], $dados['ram_slot3'], $dados['ram_slot4'], $dados['ram_modelo']
    );
    $mramStmt->execute();
    $mramId = $mramStmt->insert_id;
    $mramStmt->close();

    // 2. Memory (armazenamento)
    $memStmt = $con->prepare(
        "INSERT INTO Memory (HD1, HD2, SSD1, SSD2, modelo) VALUES (?, ?, ?, ?, ?)"
    );
    $memStmt->bind_param(
        "sssss",
        $dados['hd1'], $dados['hd2'], $dados['ssd1'], $dados['ssd2'], $dados['storage_modelo']
    );
    $memStmt->execute();
    $memId = $memStmt->insert_id;
    $memStmt->close();

    // 3. Placa de vídeo
    $placaVerifica = !empty($dados['placa_verifica']) ? 1 : 0;
    $pvStmt = $con->prepare(
        "INSERT INTO Placa_video (nome, modelo, marca, verifica) VALUES (?, ?, ?, ?)"
    );
    $pvStmt->bind_param(
        "sssi",
        $dados['placa_nome'], $dados['placa_modelo'], $dados['placa_marca'], $placaVerifica
    );
    $pvStmt->execute();
    $pvId = $pvStmt->insert_id;
    $pvStmt->close();

    // 4. Periféricos
    $periStmt = $con->prepare(
        "INSERT INTO Perifericos (monitor, teclado, estabilizador) VALUES (?, ?, ?)"
    );
    $periStmt->bind_param("sss", $dados['monitor'], $dados['teclado'], $dados['estabilizador']);
    $periStmt->execute();
    $periId = $periStmt->insert_id;
    $periStmt->close();

    // 5. Pec (agrupa tudo)
    $pecStmt = $con->prepare(
        "INSERT INTO Pec (MRAM_SET, MEMO_SET, PLAC_VID, PERI_SET, processador, motherboard, gravador_dvd, fonte, placa_rede)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $pecStmt->bind_param(
        "iiiisssss",
        $mramId, $memId, $pvId, $periId,
        $dados['processador'], $dados['motherboard'], $dados['gravador_dvd'], $dados['fonte'], $dados['placa_rede']
    );
    $pecStmt->execute();
    $pecId = $pecStmt->insert_id;
    $pecStmt->close();

    // 6. Máquina
    $statusMaquina = (int)($dados['status_maquina'] ?? 0);
    if ($idMaq === null) {
        $maqStmt = $con->prepare(
            "INSERT INTO Maquina (ID_PEC_SET, ID_LAB, status_maquina, apelido) VALUES (?, ?, ?, ?)"
        );
        $maqStmt->bind_param("iiis", $pecId, $dados['id_lab'], $statusMaquina, $dados['apelido']);
        $maqStmt->execute();
        $idMaq = $maqStmt->insert_id;
        $maqStmt->close();
    } else {
        $maqStmt = $con->prepare(
            "UPDATE Maquina SET ID_PEC_SET = ?, status_maquina = ?, apelido = ? WHERE ID_MAQ = ?"
        );
        $maqStmt->bind_param("iisi", $pecId, $statusMaquina, $dados['apelido'], $idMaq);
        $maqStmt->execute();
        $maqStmt->close();
    }

    return $idMaq;
}

/** Cria uma baia nova em um laboratório, associando a uma máquina. */
function criarBaia(mysqli $con, int $idLab, int $idMaq, int $numero): void {
    $stmt = $con->prepare("INSERT INTO Baia (ID_LAB, ID_MAQ, numero) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $idLab, $idMaq, $numero);
    $stmt->execute();
    $stmt->close();
}

/** Próximo número de baia livre, reutilizando lacunas antes de criar uma nova. */
function proximoNumeroBaia(mysqli $con, int $idLab): int {
    $query = $con->prepare("SELECT numero FROM Baia WHERE ID_LAB = ? ORDER BY numero");
    $query->bind_param("i", $idLab);
    $query->execute();
    $result = $query->get_result();
    $ocupadas = [];
    while ($row = $result->fetch_assoc()) {
        $ocupadas[(int)$row['numero']] = true;
    }
    $query->close();
    for ($numero = 1; $numero <= 48; $numero++) {
        if (!isset($ocupadas[$numero])) {
            return $numero;
        }
    }
    return 49;
}

/**
 * Desenha o relatório de ocorrências de um laboratório em um SimplePdf já
 * criado. Reaproveitado pelos dois PDFs (professor e suporte).
 */
function desenharRelatorioLaboratorioPdf(SimplePdf $pdf, int $labAtual, array $ocorrencias, string $geradoEm): void
{
    $margin = $pdf->marginX();
    $contentWidth = $pdf->contentWidth();

    // Cabeçalho com respiro consistente entre título, metadados e tabela.
    $pdf->setColor(17, 17, 17);
    $pdf->setFont('B', 18);
    $pdf->text("Relatorio - Laboratorio $labAtual", $margin, $pdf->getY());
    $pdf->setXY($margin, $pdf->getY() - 26);
    $pdf->setColor(90, 90, 90);
    $pdf->setFont('R', 10);
    $pdf->text("Gerado em $geradoEm  |  " . count($ocorrencias) . " ocorrencia(s)", $margin, $pdf->getY());
    $pdf->setXY($margin, $pdf->getY() - 30);

    if (empty($ocorrencias)) {
        $pdf->setColor(90, 90, 90);
        $pdf->setFont('R', 11);
        $pdf->text('Nenhuma ocorrencia registrada neste laboratorio.', $margin, $pdf->getY());
        return;
    }

    // Larguras fecham exatamente a área útil A4 e deixam padding interno igual.
    $colWidths = [52, 70, 132, 82, 72, $contentWidth - 52 - 70 - 132 - 82 - 72];
    $headers = ['Maquina', 'Tipo', 'Titulo', 'Status', 'Data', 'Reportado por'];
    $headerHeight = 24;
    $minRowHeight = 26;
    $cellPadX = 7;
    $lineHeight = 12;

    $drawHeader = function () use ($pdf, $margin, $colWidths, $headers, $headerHeight): void {
        $top = $pdf->getY();
        $pdf->setColor(232, 237, 242);
        $pdf->rect($margin, $top - $headerHeight, array_sum($colWidths), $headerHeight);
        $pdf->setColor(45, 58, 72);
        $pdf->setFont('B', 8.5);
        $x = $margin;
        foreach ($headers as $i => $header) {
            $pdf->text($header, $x + 7, $top - 15);
            $x += $colWidths[$i];
        }
        $pdf->setXY($margin, $top - $headerHeight);
    };

    $pdf->setFont('R', 9.2);
    $pdf->ensureSpace($headerHeight + $minRowHeight + 12);
    $drawHeader();

    foreach ($ocorrencias as $o) {
        $info = statusInfo((int)$o['status_ocorrencia']);
        $cells = [
            $o['ID_MAQ'] !== null ? ('#' . (int)$o['ID_MAQ']) : '—',
            (string)($o['tipo_problema'] ?? 'Outro'),
            (string)($o['titulo'] ?? ''),
            $info['label'],
            (string)($o['data_ocorr'] ?? ''),
            (string)($o['reportado_por'] ?? ''),
        ];
        $wrapped = [];
        $maxLines = 1;
        foreach ($cells as $i => $value) {
            $lines = $pdf->wrapText($value, max(20, $colWidths[$i] - ($cellPadX * 2)));
            $wrapped[] = $lines;
            $maxLines = max($maxLines, count($lines));
        }
        $rowHeight = max($minRowHeight, ($maxLines * $lineHeight) + 12);
        if ($pdf->ensureSpace($rowHeight + 8)) {
            $drawHeader();
        }
        $top = $pdf->getY();
        $x = $margin;
        foreach ($wrapped as $i => $lines) {
            $color = [28, 35, 42];
            if ($i === 3) {
                $color = match ($info['class']) {
                    'is-error' => [190, 48, 67],
                    'is-warn' => [145, 96, 10],
                    default => [25, 132, 79],
                };
            }
            $pdf->setColor(...$color);
            foreach ($lines as $lineIndex => $line) {
                $pdf->text($line, $x + $cellPadX, $top - 15 - ($lineIndex * $lineHeight));
            }
            $x += $colWidths[$i];
        }
        $pdf->setColor(215, 221, 227);
        $pdf->line($margin, $top - $rowHeight, $margin + array_sum($colWidths), $top - $rowHeight, 0.55);
        $pdf->setXY($margin, $top - $rowHeight);
    }
}

/**
 * Registra um evento no histórico (log) de uma ocorrência/máquina.
 * $acao: 'criada' | 'concluida'
 * O registro é independente da linha em Ocorrencias, então continua
 * existindo mesmo depois que a ocorrência é excluída/concluída.
 */
function registrarHistorico(
    mysqli $con,
    int $idOcorrencia,
    int $idLab,
    ?int $idMaq,
    string $titulo,
    string $tipoProblema,
    int $statusOcorrencia,
    string $acao,
    int $idUser
): void {
    $nomeStmt = $con->prepare("SELECT nome FROM Usuarios WHERE ID_USER = ?");
    $nomeStmt->bind_param("i", $idUser);
    $nomeStmt->execute();
    $nomeStmt->bind_result($nomeUsuario);
    $nomeStmt->fetch();
    $nomeStmt->close();
    $nomeUsuario = $nomeUsuario ?? 'Usuário removido';

    $stmt = $con->prepare(
        "INSERT INTO Historico (ID_OCORR, ID_LAB, ID_MAQ, titulo, tipo_problema, status_ocorrencia, acao, ID_USER, usuario_nome)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "iiissisis",
        $idOcorrencia,
        $idLab,
        $idMaq,
        $titulo,
        $tipoProblema,
        $statusOcorrencia,
        $acao,
        $idUser,
        $nomeUsuario
    );
    $stmt->execute();
    $stmt->close();
}

/**
 * Busca o histórico com filtros opcionais (laboratório e/ou máquina).
 * Ordenado do mais recente para o mais antigo.
 */
function buscarHistorico(mysqli $con, ?int $idLab = null, ?int $idMaq = null): array {
    $sql = "SELECT * FROM Historico WHERE 1=1";
    $types = "";
    $params = [];

    if ($idLab !== null) {
        $sql .= " AND ID_LAB = ?";
        $types .= "i";
        $params[] = $idLab;
    }
    if ($idMaq !== null) {
        $sql .= " AND ID_MAQ = ?";
        $types .= "i";
        $params[] = $idMaq;
    }
    $sql .= " ORDER BY data_evento DESC, ID_HIST DESC";

    $stmt = $con->prepare($sql);
    if ($types !== "") {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

/** Rótulo amigável para o campo "acao" do histórico. */
function acaoLabel(string $acao): string {
    return match ($acao) {
        'criada' => 'Ocorrência registrada',
        'concluida' => 'Ocorrência concluída',
        default => ucfirst($acao),
    };
}

/** Navegação principal do topo, compartilhada entre professor e suporte. */
function renderTopbar(string $perfil, string $paginaAtiva): void {
    $base = "/$perfil";
    $itens = [
        'inicio'      => ['label' => 'Laboratórios', 'href' => "$base/inicio.php"],
    ];
    // Cadastro de máquinas/peças é responsabilidade do suporte; o professor
    // só visualiza (dentro da própria tela de laboratório).
    if ($perfil === 'suporte') {
        $itens['maquinas'] = ['label' => 'Máquinas', 'href' => "$base/maquinas.php"];
    }
    $itens['relatorio'] = ['label' => 'Novo relatório', 'href' => "$base/relatorio.php"];
    $itens['historico'] = ['label' => 'Histórico', 'href' => "$base/historico.php"];
    ?>
    <header class="app-topbar">
        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <div class="app-brand"><span class="dot"></span>BetterOrganized</div>
        <label for="nav-toggle" class="nav-toggle-label" aria-label="Abrir menu"><span></span></label>
        <nav class="app-nav">
            <?php foreach ($itens as $key => $item): ?>
                <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $key === $paginaAtiva ? 'is-active' : '' ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="app-logout">
            <form action="/logout.php" method="post">
                <button type="submit" name="logout_button">Sair</button>
            </form>
        </div>
    </header>
    <?php
}
