<?php
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION["ID_USER"])) {
    header("Location: ../../../index.php");
    exit();
}

// Obtém as ocorrências do banco de dados
$query_ocorrencias = $con->query("SELECT ID_OCORR, ID_BAIA, titulo FROM Ocorrencias");
$ocorrencias = $query_ocorrencias->fetch_all(MYSQLI_ASSOC);

if (empty($ocorrencias)) {
    // Se não houver ocorrências, defina $ocorrencias como um array vazio para evitar erros
    $ocorrencias = [];
}

if (isset($_POST['submit_button'])) {
    $id_ocorrencia = (int) ($_POST['id_ocorrencia'] ?? 0);
    $id_baia = (int) ($_POST['id_baia'] ?? 0);

    // Função para obter detalhes da ocorrência
    function getDetalhesOcorrencia($id_ocorrencia, $con) {
        $query = $con->prepare("SELECT titulo, data_ocorr, descricao, ID_BAIA FROM Ocorrencias WHERE ID_OCORR = ?");
        $query->bind_param("i", $id_ocorrencia);
        $query->execute();
        $query->bind_result($titulo, $data_ocorr, $descricao, $id_baia);
        $query->fetch();
        $query->close();

        return compact('titulo', 'data_ocorr', 'descricao', 'id_baia');
    }

    $detalhes_ocorrencia = getDetalhesOcorrencia($id_ocorrencia, $con);
} else {
    // Redirecione se os dados do formulário não foram enviados corretamente
    header("Location: detalhes_ocorrencia.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="view_rel.css">
</head>

<body>
  <div class="card">
    <?php if (!empty($detalhes_ocorrencia)) : ?>
      <h2><?= htmlspecialchars($detalhes_ocorrencia['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
      <p>Data: <?= htmlspecialchars($detalhes_ocorrencia['data_ocorr'], ENT_QUOTES, 'UTF-8') ?></p>
      <p>Descrição: <?= nl2br(htmlspecialchars($detalhes_ocorrencia['descricao'], ENT_QUOTES, 'UTF-8')) ?></p>
      <div class="info">
        <p><i class="icon fas fa-laptop"></i>ID da Baia: <?= (int) $detalhes_ocorrencia['id_baia'] ?></p>
      </div>
      <div class="buttons">
        <a href="../sup.php" class="back-button"><button type="button">← Voltar</button></a>
        <a href="gerar_pdf.php?id_ocorrencia=<?= (int) $id_ocorrencia ?>" class="pdf-button">Gerar PDF</a>
        <form action="delete_ocorrencia.php" method="post" class="delete-form">
          <input type="hidden" name="id_ocorrencia" value="<?= (int) $id_ocorrencia ?>">
          <button type="submit" name="concluir_relatorio">Concluir Relatório</button>
        </form>
      </div>
    <?php else : ?>
      <p>Nenhuma ocorrência selecionada.</p>
    <?php endif; ?>
  </div>

  <!-- Restante do seu HTML -->

</body>

</html>
