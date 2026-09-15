<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../config/helpers.php';

if (!isset($_SESSION["ID_USER"])) {
    header("Location: /index.php");
    exit();
}
requireRole(0);

$labIDs = [1, 2, 3, 4]; // laboratórios disponíveis para o suporte
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BetterOrganized - Laboratórios</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/inicio.css">
</head>
<body>
    <div class="app-shell">
        <?php renderTopbar('suporte', 'inicio'); ?>
        <main class="app-main">
            <div class="page-head">
                <h1>Laboratórios</h1>
                <p>Selecione um laboratório para ver o mapa de estações</p>
            </div>
            <div class="lab-grid">
                <?php foreach ($labIDs as $labID):
                    $status = getStatusLaboratorio($con, $labID);
                    $info = statusInfo($status);
                    $quantMaquinas = getQuantMaquinas($con, $labID);
                ?>
                    <div class="lab-card">
                        <div class="lab-card-head">
                            <h2>Laboratório <?= $labID ?></h2>
                            <span class="status-pill <?= $info['class'] ?>">
                                <span class="status-dot <?= $info['class'] ?>"></span><?= $info['label'] ?>
                            </span>
                        </div>
                        <p><?= $quantMaquinas ?> máquina(s) cadastrada(s)</p>
                        <a class="btn btn-accent btn-block" href="/suporte/laboratorio.php?lab=<?= $labID ?>">Entrar</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>