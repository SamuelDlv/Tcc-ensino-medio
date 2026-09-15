<?php
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION["ID_USER"])) {
    header("Location: ../../index.php");
    exit();
}
// IDs fixos dos laboratórios
$labIDs = array(1, 2, 3); // Substitua pelos IDs dos seus laboratórios

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecione o Laboratório</title>
    <link rel="stylesheet" href="Inicio.css">
</head>
<body>
    <h1>Better Organized</h1>
    <div class="container">
        <?php
        // Itera sobre os IDs fixos dos laboratórios
        foreach ($labIDs as $labID) {
            // Query para obter o status do laboratório com base no status_ocorrencia
            $sql = "SELECT COALESCE(MAX(status_ocorrencia), 0) AS max_status_ocorrencia FROM Ocorrencias WHERE ID_LAB = $labID";
            $result = $con->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $maxStatusOcorrencia = $row["max_status_ocorrencia"];
                $labStatus = ($maxStatusOcorrencia == 1) ? 1 : 0;

                echo '<div class="card">';
                echo '<h2><span class="status" style="background-color: ' . (($labStatus == 1) ? 'red' : 'green') . ';"></span>' . "Laboratório $labID" . '</h2>';
                // Adicione um link com a URL desejada para o Laboratório 1
                if ($labID == 1) {
                    echo '<a href="../Laboratorio1/better.php"><button>Entrar</button></a>';
                } else {
                    echo '<button>Entrar</button>';
                }
                echo '</div>';
            } else {
                echo "Erro ao obter status do laboratório $labID";
            }
        }

        // Feche a conexão
        $con->close();
        ?>
    </div>
</body>
</html>
