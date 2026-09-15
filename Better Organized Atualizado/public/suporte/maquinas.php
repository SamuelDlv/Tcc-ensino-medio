<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../config/helpers.php';

if (!isset($_SESSION["ID_USER"])) {
    header("Location: /index.php");
    exit();
}
requireRole(0);

$labAtual = isset($_GET['lab']) ? (int)$_GET['lab'] : 1;
if ($labAtual < 1 || $labAtual > 4) {
    $labAtual = 1;
}

$erro = null;

// ---------- Exclusão ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_maquina'])) {
    $idExcluir = (int)$_POST['id_maquina'];
    excluirMaquina($con, $idExcluir);
    header("Location: /suporte/maquinas.php?lab=" . $labAtual);
    exit();
}

// ---------- Criação/edição ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_maquina'])) {
    $idMaqEdit = isset($_POST['id_maquina']) && $_POST['id_maquina'] !== '' ? (int)$_POST['id_maquina'] : null;

    $dados = [
        'id_lab'          => (int)$_POST['id_lab'],
        'status_maquina'  => (int)($_POST['status_maquina'] ?? 0),
        'apelido'         => '',
        'processador'     => trim($_POST['processador'] ?? ''),
        'motherboard'     => trim($_POST['motherboard'] ?? ''),
        'gravador_dvd'    => trim($_POST['gravador_dvd'] ?? ''),
        'fonte'           => trim($_POST['fonte'] ?? ''),
        'placa_rede'      => trim($_POST['placa_rede'] ?? ''),
        'ram_slot1'       => trim($_POST['ram_slot1'] ?? ''),
        'ram_slot2'       => trim($_POST['ram_slot2'] ?? ''),
        'ram_slot3'       => trim($_POST['ram_slot3'] ?? ''),
        'ram_slot4'       => trim($_POST['ram_slot4'] ?? ''),
        'ram_modelo'      => trim($_POST['ram_modelo'] ?? ''),
        'hd1'             => trim($_POST['hd1'] ?? ''),
        'hd2'             => trim($_POST['hd2'] ?? ''),
        'ssd1'            => trim($_POST['ssd1'] ?? ''),
        'ssd2'            => trim($_POST['ssd2'] ?? ''),
        'storage_modelo'  => trim($_POST['storage_modelo'] ?? ''),
        'placa_nome'      => trim($_POST['placa_nome'] ?? ''),
        'placa_modelo'    => trim($_POST['placa_modelo'] ?? ''),
        'placa_marca'     => trim($_POST['placa_marca'] ?? ''),
        'placa_verifica'  => isset($_POST['placa_verifica']) ? 1 : 0,
        'monitor'         => trim($_POST['monitor'] ?? ''),
        'teclado'         => trim($_POST['teclado'] ?? ''),
        'estabilizador'   => trim($_POST['estabilizador'] ?? ''),
    ];

    $idMaq = salvarMaquinaCompleta($con, $dados, $idMaqEdit);

    // A baia é sempre criada automaticamente: a próxima posição livre do laboratório.
    $baiaQuery = $con->prepare("SELECT ID_BAIA, ID_LAB, numero FROM Baia WHERE ID_MAQ = ?");
    $baiaQuery->bind_param("i", $idMaq);
    $baiaQuery->execute();
    $baiaQuery->bind_result($idBaiaExistente, $labBaiaAtual, $numeroBaiaAtualDb);
    $temBaia = $baiaQuery->fetch();
    $baiaQuery->close();
    if ($temBaia && (int)$labBaiaAtual === (int)$dados['id_lab']) {
        // Mantém a posição da máquina quando ela continua no mesmo laboratório.
    } elseif ($temBaia) {
        $novoNumero = proximoNumeroBaia($con, (int)$dados['id_lab']);
        $upd = $con->prepare("UPDATE Baia SET numero = ?, ID_LAB = ? WHERE ID_BAIA = ?");
        $upd->bind_param("iii", $novoNumero, $dados['id_lab'], $idBaiaExistente);
        $upd->execute();
        $upd->close();
    } else {
        $novoNumero = proximoNumeroBaia($con, (int)$dados['id_lab']);
        criarBaia($con, (int)$dados['id_lab'], $idMaq, $novoNumero);
    }
    header("Location: /suporte/maquinas.php?lab=" . $dados['id_lab'] . '&maquina=' . $idMaq . '#form-maquina');
    exit();
}

// ---------- Dados para exibição ----------
$maquinas = listarMaquinasDoLab($con, $labAtual);

$maquinaEdicao = null;
$numeroBaiaAtual = '';
if (isset($_GET['maquina'])) {
    $idMaquinaGet = (int)$_GET['maquina'];
    $maquinaEdicao = buscarMaquinaCompleta($con, $idMaquinaGet);
    if ($maquinaEdicao) {
        $bq = $con->prepare("SELECT numero FROM Baia WHERE ID_MAQ = ?");
        $bq->bind_param("i", $idMaquinaGet);
        $bq->execute();
        $bq->bind_result($numeroBaiaAtual);
        if (!$bq->fetch()) {
            $numeroBaiaAtual = '';
        }
        $bq->close();
    }
}

function val($arr, $key) {
    return htmlspecialchars((string)($arr[$key] ?? ''));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BetterOrganized - Máquinas</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/relatorio.css">
    <link rel="stylesheet" href="/assets/css/maquinas.css">
</head>
<body>
    <div class="app-shell">
        <?php renderTopbar('suporte', 'maquinas'); ?>
        <main class="app-main">
            <div class="maquinas-head">
                <div>
                    <h1>Máquinas — Laboratório <?= $labAtual ?></h1>
                    <p>Cadastre, edite ou remova as máquinas e suas peças.</p>
                </div>
                <div class="lab-switch" style="margin:0;">
                    <label for="lab-select">Laboratório</label>
                    <select id="lab-select" onchange="window.location.href='/suporte/maquinas.php?lab='+this.value">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>" <?= $labAtual == $i ? 'selected' : '' ?>>Laboratório <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="maquinas-table-wrap">
                <?php if (empty($maquinas)): ?>
                    <p class="empty-state">Nenhuma máquina cadastrada neste laboratório ainda.</p>
                <?php else: ?>
                    <table class="maquinas-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Baia</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maquinas as $m): $info = statusInfo((int)($m['status_maquina'] ?? 0)); ?>
                                <tr>
                                    <td><span class="machine-id">#<?= (int)$m['ID_MAQ'] ?></span></td>
                                    <td><?= $m['numero'] !== null ? (int)$m['numero'] : '—' ?></td>
                                    <td>
                                        <span class="status-pill <?= $info['class'] ?>">
                                            <span class="status-dot <?= $info['class'] ?>"></span><?= $info['label'] ?>
                                        </span>
                                    </td>
                                    <td class="row-actions">
                                        <a class="btn btn-ghost" href="/suporte/maquinas.php?lab=<?= $labAtual ?>&maquina=<?= (int)$m['ID_MAQ'] ?>#form-maquina">Editar</a>
                                        <form method="post" onsubmit="return confirm('Excluir esta máquina? Isso também remove a baia associada.');">
                                            <input type="hidden" name="id_maquina" value="<?= (int)$m['ID_MAQ'] ?>">
                                            <button type="submit" name="excluir_maquina" class="btn btn-ghost">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="maquina-form-wrap" id="form-maquina">
                <h2><?= $maquinaEdicao ? 'Editar máquina #' . (int)$maquinaEdicao['ID_MAQ'] : 'Cadastrar nova máquina' ?></h2><div class="machine-id-banner"><?php if ($maquinaEdicao): ?><strong>ID da máquina: #<?= (int)$maquinaEdicao['ID_MAQ'] ?></strong><span>Este identificador será usado nos relatórios.</span><?php else: ?><strong>Próximo ID automático</strong><span>O sistema gera o ID ao salvar e ele não se repete.</span><?php endif; ?></div>
                <div class="report-card">
                    <form method="POST">
                        <input type="hidden" name="id_maquina" value="<?= $maquinaEdicao ? (int)$maquinaEdicao['ID_MAQ'] : '' ?>">

                        <div class="form-section">
                            <h3>Identificação</h3>
                            <div class="field-row">
                                <div class="field">
                                    <label for="id_lab">Laboratório</label>
                                    <select name="id_lab" id="id_lab" required>
                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($maquinaEdicao['ID_LAB'] ?? $labAtual) == $i ? 'selected' : '' ?>>Laboratório <?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field field-full">
                                    <div class="auto-baia-note"><strong>Baia automática</strong><span>O sistema atribui a próxima posição livre deste laboratório ao salvar.</span></div>
                                </div>
                                <div class="field">
                                    <label>Status</label>
                                    <div class="status-choice">
                                        <input type="radio" id="status-ok" name="status_maquina" value="0" <?= (($maquinaEdicao['status_maquina'] ?? 0) == 0) ? 'checked' : '' ?>>
                                        <label for="status-ok"><span class="status-dot is-ok"></span>Funcionando</label>

                                        <input type="radio" id="status-defeito" name="status_maquina" value="1" <?= (($maquinaEdicao['status_maquina'] ?? 0) == 1) ? 'checked' : '' ?>>
                                        <label for="status-defeito"><span class="status-dot is-error"></span>Com defeito</label>

                                        <input type="radio" id="status-manutencao" name="status_maquina" value="2" <?= (($maquinaEdicao['status_maquina'] ?? 0) == 2) ? 'checked' : '' ?>>
                                        <label for="status-manutencao"><span class="status-dot is-warn"></span>Manutenção</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Processador e placa-mãe</h3>
                            <div class="field-row">
                                <div class="field">
                                    <label for="processador">Processador</label>
                                    <input type="text" id="processador" name="processador" value="<?= val($maquinaEdicao, 'processador') ?>">
                                </div>
                                <div class="field">
                                    <label for="motherboard">Motherboard</label>
                                    <input type="text" id="motherboard" name="motherboard" value="<?= val($maquinaEdicao, 'motherboard') ?>">
                                </div>
                            </div>
                            <div class="field-row-3">
                                <div class="field">
                                    <label for="gravador_dvd">Gravador de DVD</label>
                                    <input type="text" id="gravador_dvd" name="gravador_dvd" value="<?= val($maquinaEdicao, 'gravador_dvd') ?>">
                                </div>
                                <div class="field">
                                    <label for="fonte">Fonte</label>
                                    <input type="text" id="fonte" name="fonte" value="<?= val($maquinaEdicao, 'fonte') ?>">
                                </div>
                                <div class="field">
                                    <label for="placa_rede">Placa de rede</label>
                                    <input type="text" id="placa_rede" name="placa_rede" value="<?= val($maquinaEdicao, 'placa_rede') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Memória RAM</h3>
                            <div class="field-row-3">
                                <div class="field">
                                    <label for="ram_slot1">Slot 1</label>
                                    <input type="text" id="ram_slot1" name="ram_slot1" value="<?= val($maquinaEdicao, 'slot1') ?>">
                                </div>
                                <div class="field">
                                    <label for="ram_slot2">Slot 2</label>
                                    <input type="text" id="ram_slot2" name="ram_slot2" value="<?= val($maquinaEdicao, 'slot2') ?>">
                                </div>
                                <div class="field">
                                    <label for="ram_slot3">Slot 3</label>
                                    <input type="text" id="ram_slot3" name="ram_slot3" value="<?= val($maquinaEdicao, 'slot3') ?>">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field">
                                    <label for="ram_slot4">Slot 4</label>
                                    <input type="text" id="ram_slot4" name="ram_slot4" value="<?= val($maquinaEdicao, 'slot4') ?>">
                                </div>
                                <div class="field">
                                    <label for="ram_modelo">Modelo</label>
                                    <input type="text" id="ram_modelo" name="ram_modelo" value="<?= val($maquinaEdicao, 'ram_modelo') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Armazenamento</h3>
                            <div class="field-row-3">
                                <div class="field">
                                    <label for="hd1">HD 1</label>
                                    <input type="text" id="hd1" name="hd1" value="<?= val($maquinaEdicao, 'HD1') ?>">
                                </div>
                                <div class="field">
                                    <label for="hd2">HD 2</label>
                                    <input type="text" id="hd2" name="hd2" value="<?= val($maquinaEdicao, 'HD2') ?>">
                                </div>
                                <div class="field">
                                    <label for="ssd1">SSD 1</label>
                                    <input type="text" id="ssd1" name="ssd1" value="<?= val($maquinaEdicao, 'SSD1') ?>">
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field">
                                    <label for="ssd2">SSD 2</label>
                                    <input type="text" id="ssd2" name="ssd2" value="<?= val($maquinaEdicao, 'SSD2') ?>">
                                </div>
                                <div class="field">
                                    <label for="storage_modelo">Modelo</label>
                                    <input type="text" id="storage_modelo" name="storage_modelo" value="<?= val($maquinaEdicao, 'storage_modelo') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Placa de vídeo</h3>
                            <div class="field-row-3">
                                <div class="field">
                                    <label for="placa_nome">Nome</label>
                                    <input type="text" id="placa_nome" name="placa_nome" value="<?= val($maquinaEdicao, 'placa_nome') ?>">
                                </div>
                                <div class="field">
                                    <label for="placa_modelo">Modelo</label>
                                    <input type="text" id="placa_modelo" name="placa_modelo" value="<?= val($maquinaEdicao, 'placa_modelo') ?>">
                                </div>
                                <div class="field">
                                    <label for="placa_marca">Marca</label>
                                    <input type="text" id="placa_marca" name="placa_marca" value="<?= val($maquinaEdicao, 'placa_marca') ?>">
                                </div>
                            </div>
                            <label class="field-check">
                                <input type="checkbox" name="placa_verifica" <?= !empty($maquinaEdicao['placa_verifica']) ? 'checked' : '' ?>>
                                Placa de vídeo verificada/testada
                            </label>
                        </div>

                        <div class="form-section">
                            <h3>Periféricos</h3>
                            <div class="field-row-3">
                                <div class="field">
                                    <label for="monitor">Monitor</label>
                                    <input type="text" id="monitor" name="monitor" value="<?= val($maquinaEdicao, 'monitor') ?>">
                                </div>
                                <div class="field">
                                    <label for="teclado">Teclado</label>
                                    <input type="text" id="teclado" name="teclado" value="<?= val($maquinaEdicao, 'teclado') ?>">
                                </div>
                                <div class="field">
                                    <label for="estabilizador">Estabilizador</label>
                                    <input type="text" id="estabilizador" name="estabilizador" value="<?= val($maquinaEdicao, 'estabilizador') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <?php if ($maquinaEdicao): ?>
                                <a href="/suporte/maquinas.php?lab=<?= $labAtual ?>" class="btn btn-ghost">Cancelar edição</a>
                            <?php endif; ?>
                            <button class="btn btn-accent btn-block" name="salvar_maquina"><?= $maquinaEdicao ? 'Salvar alterações' : 'Cadastrar máquina' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>