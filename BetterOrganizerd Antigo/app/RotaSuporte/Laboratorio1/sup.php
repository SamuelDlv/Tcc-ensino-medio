<?php

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION["ID_USER"])) {
    header("Location: ../../index.php");
    exit();
}

function getStatusMaquina($id_baia, $con, $id_lab) {
    $query = $con->prepare("SELECT status_ocorrencia FROM Ocorrencias WHERE ID_BAIA = ? AND ID_LAB = ?");
    $query->bind_param("ii", $id_baia, $id_lab);
    $query->execute();
    $query->bind_result($status_ocorrencia);
    $query->fetch();
    $query->close();

    return $status_ocorrencia;
}

function getOcorrencias($con, $id_lab) {
    // Adicione a cláusula WHERE para filtrar por ID_LAB
    $query = $con->prepare("SELECT ID_OCORR, ID_BAIA, titulo FROM Ocorrencias WHERE ID_LAB = ? ORDER BY ID_BAIA");
    $query->bind_param("i", $id_lab);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    return $result->fetch_all(MYSQLI_ASSOC);
}

$ocorrencias = getOcorrencias($con, 1); // Altere o valor para o ID_LAB desejado
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
        <div class="content">
             <?php
// Inicie ou retome a sessão

// Verifique se o usuário está autenticado
if (isset($_SESSION["ID_USER"])) {
    echo '<form action="logout.php" method="post">';
    echo '<button type="submit" name="logout_button">Logout</button>';
    echo '</form>';
}
?>
<hr>
            <!-- Seu conteúdo existente -->
            <div class="search-box">

    <form action="Relatorio/relatorio.php" method="post">
        <button type="submit" style="width: 100%;">Criar Relatório</button>
    </form>
    <hr>
    <!-- Gere botões dinamicamente com base na tabela Ocorrencias -->
    <?php foreach ($ocorrencias as $ocorrencia) : ?>
        <div class="blank-card" style="width: 100%;">
            <!-- Adiciona um formulário para cada botão -->
            <form action="Detalhes/detalhes_ocorrencia.php" method="post">
                <input type="hidden" name="id_ocorrencia" value="<?= (int) $ocorrencia['ID_OCORR'] ?>">
                <input type="hidden" name="id_baia" value="<?= (int) $ocorrencia['ID_BAIA'] ?>">
                <button type="submit" name="submit_button" style="width: 100%;">
                    <?= htmlspecialchars($ocorrencia['titulo'] . ' - Baia ' . (int) $ocorrencia['ID_BAIA'], ENT_QUOTES, 'UTF-8') ?>
                </button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

        </div>
    </div>















    <div class="movie-container">
        <label for="movie">Selecione o laboratório</label>
        <select id="movie" onchange="redirect()">
            <option value="1">laboratório 1</option>
            <option value="2">laboratório 2</option>
            <option value="3">laboratório 3</option>
            <option value="4">laboratório 4</option>
        </select>
    </div>

    <script>
        function redirect() {
            var selectedValue = document.getElementById("movie").value;
            var url;

            switch (selectedValue) {
                case "1":
                    url = "sup.php";
                    break;
                case "2":
                    url = "#";
                    break;
                case "3":
                    url = "#";
                    break;
                case "4":
                    url = "#";
                    break;
                default:
                    url = "#";
            }

            window.location.href = url;
        }
    </script>


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
                $status_info = getStatusMaquina($id_baia, $con, 1); // Passa o ID_LAB desejado
                $class = ($status_info == 1) ? 'seat-red' : (($status_info == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">

             <?php
              for ($id_baia = 9; $id_baia <= 16; $id_baia++) {
                $status_info = getStatusMaquina($id_baia, $con, 1); // Passa o ID_LAB desejado
                $class = ($status_info == 1) ? 'seat-red' : (($status_info == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
              for ($id_baia = 17; $id_baia <= 24; $id_baia++) {
                $status_info = getStatusMaquina($id_baia, $con, 1); // Passa o ID_LAB desejado
                $class = ($status_info == 1) ? 'seat-red' : (($status_info == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
              for ($id_baia = 25; $id_baia <= 32; $id_baia++) {
                $status_info = getStatusMaquina($id_baia, $con, 1); // Passa o ID_LAB desejado
                $class = ($status_info == 1) ? 'seat-red' : (($status_info == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
              for ($id_baia = 33; $id_baia <= 40; $id_baia++) {
                $status_info = getStatusMaquina($id_baia, $con, 1); // Passa o ID_LAB desejado
                $class = ($status_info == 1) ? 'seat-red' : (($status_info == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>

        <div class="row">
            <?php
              for ($id_baia = 41; $id_baia <= 48; $id_baia++) {
                $status_info = getStatusMaquina($id_baia, $con, 1); // Passa o ID_LAB desejado
                $class = ($status_info == 1) ? 'seat-red' : (($status_info == 2) ? 'seat-maintenance' : '');
                echo '<div class="seat ' . $class . '"><span>' . $id_baia . '</span></div>';
            }
            ?>
        </div>
    </div>

</body>
</html>
