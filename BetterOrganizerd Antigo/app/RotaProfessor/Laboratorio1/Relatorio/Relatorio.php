<?php
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION["ID_USER"])) {
    header("Location: ../../../index.php");
    exit();
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['sub'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $data = $_POST['data'] ?? '';
    $user = (int) $_SESSION["ID_USER"];
    $status = (int) ($_POST['status_ocorrencia'] ?? 1);
    $id_lab = (int) ($_POST['escolha'] ?? 0);
    $id_baia = (int) ($_POST['ID_BAIA'] ?? 0);

    if ($titulo === '' || $descricao === '' || $data === '' || $id_lab < 1 || $id_lab > 4 || $id_baia < 1 || $id_baia > 48 || !in_array($status, [1, 2], true)) {
        $mensagem = 'Preencha os campos corretamente. A baia deve estar entre 1 e 48.';
        $tipo_mensagem = 'erro';
    } else {
        $query = $con->prepare("INSERT INTO Ocorrencias (titulo, descricao, data_ocorr, ID_USER, status_ocorrencia, ID_LAB, ID_BAIA) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->bind_param("sssiiii", $titulo, $descricao, $data, $user, $status, $id_lab, $id_baia);
        if ($query->execute()) {
            $mensagem = 'Relatório registrado com sucesso.';
            $tipo_mensagem = 'sucesso';
        } else {
            $mensagem = 'Não foi possível registrar o relatório.';
            $tipo_mensagem = 'erro';
        }
        $query->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Problemas</title>
    <link rel="stylesheet" href="Relatorio.css">
</head>
<body>
    <a href="../better.php" class="back-button">← Voltar</a>
    <h1>Relatório de Problemas</h1>
    <?php if ($mensagem !== '') : ?>
        <p class="mensagem <?= htmlspecialchars($tipo_mensagem, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>
    <form method="POST" class="report-form">
        <label for="ID_BAIA">ID da baia</label>
        <input id="ID_BAIA" type="number" name="ID_BAIA" min="1" max="48" required>
        <label for="titulo">Título</label>
        <input id="titulo" type="text" name="titulo" maxlength="255" required>
        <label for="laboratorio">Selecione o laboratório</label>
        <select name="escolha" id="laboratorio" required>
            <option value="1">Laboratório 1</option>
            <option value="2">Laboratório 2</option>
            <option value="3">Laboratório 3</option>
            <option value="4">Laboratório 4</option>
        </select>
        <label for="status_ocorrencia">Situação do equipamento</label>
        <select name="status_ocorrencia" id="status_ocorrencia" required>
            <option value="1">Com defeito</option>
            <option value="2">Em manutenção</option>
        </select>
        <label for="data">Data</label>
        <input id="data" type="date" name="data" value="<?= date('Y-m-d') ?>" required>
        <label for="descricao">Relatório</label>
        <textarea id="descricao" name="descricao" rows="5" required></textarea>
        <div class="button-container">
            <button type="submit" name="sub">Registrar relatório</button>
        </div>
    </form>
</body>
</html>
