<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION["ID_USER"])) {
    header("Location: ../../index.php");
    exit();
}

// Função para obter o status da máquina do banco de dados
function getStatusMaquina($id_baia, $con) {
    $query = $con->prepare("SELECT status_ocorrencia FROM Ocorrencias WHERE ID_BAIA = ? AND ID_LAB = 1");
    $query->bind_param("i", $id_baia);
    $query->execute();
    $query->bind_result($status_ocorrencia);
    $query->fetch();
    $query->close();

    return $status_ocorrencia;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Better Organized</title>
    <link rel="stylesheet" href="Suport.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <style>
        /* Adicione estilos para a cadeira vermelha aqui */
        .seat-red {
            background-color: red;
        }
    .seat-maintenance { background-color: #facc15; }
    </style>
    <div class="side-menu">
        <div class="button">
            <a href="Relatorio/Relatorio.php"><button>Criar relatorio</button></a>
            <hr>
            <?php
            // Inicie ou retome a sessão

            // Verifique se o usuário está autenticado
            if (isset($_SESSION["ID_USER"])) {
                echo '<form action="logout.php" method="post">';
                echo '<button type="submit" name="logout_button">Logout</button>';
                echo '</form>';
            }
            ?>
        </div>
    </div>
    <div class="movie-container">
        <label for="">Selecione o laboratório</label>
        <select id="movie">
            <option value="1">laboratório 1</option>
            <option value="2">laboratório 2</option>
            <option value="3">laboratório 3</option>
            <option value="4">laboratório 4</option>
        </select>
    </div>

    <ul class="showcase">
        <li>
            <div class="seat"></div>
            <small>Funcionando</small>
        </li>

        <li>
            <div class="seat Defeito"></div>
            <small>Com Defeito</small>
        </li>

        <li>
            <div class="seat Manutenção"></div>
            <small>Manutenção</small>
        </li>
    </ul>

    <div class="container">
        <div class="screen"></div>
        <div class="row">
            <?php
            for ($id_baia = 1; $id_baia <= 8; $id_baia++) {
                // Substitua '1' pelo ID_MAQ específico que você deseja obter o status.
                $status_ocorrencia = getStatusMaquina($id_baia, $con);

                // Verifica o status antes de adicionar a classe CSS.
                $class = ($status_ocorrencia == 1) ? 'seat-red' : (($status_ocorrencia == 2) ? 'seat-maintenance' : '');

                // Imprime a cadeira com a classe CSS determinada pelo status da máquina.
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
            for ($id_baia = 9; $id_baia <= 16; $id_baia++) {
                $status_ocorrencia = getStatusMaquina($id_baia, $con);
                $class = ($status_ocorrencia == 1) ? 'seat-red' : (($status_ocorrencia == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
            for ($id_baia = 17; $id_baia <= 24; $id_baia++) {
                $status_ocorrencia = getStatusMaquina($id_baia, $con);
                $class = ($status_ocorrencia == 1) ? 'seat-red' : (($status_ocorrencia == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
            for ($id_baia = 25; $id_baia <= 32; $id_baia++) {
                $status_ocorrencia = getStatusMaquina($id_baia, $con);
                $class = ($status_ocorrencia == 1) ? 'seat-red' : (($status_ocorrencia == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
            for ($id_baia = 33; $id_baia <= 40; $id_baia++) {
                $status_ocorrencia = getStatusMaquina($id_baia, $con);
                $class = ($status_ocorrencia == 1) ? 'seat-red' : (($status_ocorrencia == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
            for ($id_baia = 41; $id_baia <= 48; $id_baia++) {
                $status_ocorrencia = getStatusMaquina($id_baia, $con);
                $class = ($status_ocorrencia == 1) ? 'seat-red' : (($status_ocorrencia == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

    </div>

</body>
</html>
