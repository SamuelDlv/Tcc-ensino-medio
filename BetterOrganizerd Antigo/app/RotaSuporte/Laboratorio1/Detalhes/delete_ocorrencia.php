<?php
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['ID_USER'])) {
    header('Location: ../../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['concluir_relatorio'])) {
    header('Location: ../sup.php');
    exit();
}

$id_ocorrencia = (int) ($_POST['id_ocorrencia'] ?? 0);
if ($id_ocorrencia < 1) {
    header('Location: ../sup.php');
    exit();
}

$query = $con->prepare('DELETE FROM Ocorrencias WHERE ID_OCORR = ?');
$query->bind_param('i', $id_ocorrencia);
$query->execute();
$query->close();

header('Location: ../sup.php');
exit();
