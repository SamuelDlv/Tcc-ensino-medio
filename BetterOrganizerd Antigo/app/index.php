<?php
require_once __DIR__ . '/config/database.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub'])) {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $query = $con->prepare('SELECT ID_USER, senha, funcao FROM Usuarios WHERE email = ? LIMIT 1');
    $query->bind_param('s', $email);
    $query->execute();
    $usuario = $query->get_result()->fetch_assoc();
    $query->close();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['ID_USER'] = (int) $usuario['ID_USER'];
        $destino = ((int) $usuario['funcao'] === 1)
            ? 'RotaProfessor/TelaInicio/Inicio.php'
            : 'RotaSuporte/TelaInicio/Inicio.php';
        header("Location: $destino");
        exit();
    }

    $mensagem = 'E-mail ou senha inválidos.';
    $tipo_mensagem = 'erro';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub2'])) {
    $nome = trim($_POST['name'] ?? '');
    $email = trim($_POST['email2'] ?? '');
    $senha = $_POST['senha2'] ?? '';
    $funcao = (int) ($_POST['escolha'] ?? -1);

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 8 || !in_array($funcao, [0, 1], true)) {
        $mensagem = 'Preencha os dados corretamente. A senha deve ter pelo menos 8 caracteres.';
        $tipo_mensagem = 'erro';
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $query = $con->prepare('INSERT INTO Usuarios (email, senha, nome, funcao, escolha) VALUES (?, ?, ?, ?, ?)');
            $query->bind_param('sssii', $email, $senha_hash, $nome, $funcao, $funcao);
            $query->execute();
            $mensagem = 'Usuário cadastrado com sucesso. Faça login.';
            $tipo_mensagem = 'sucesso';
            $query->close();
        } catch (mysqli_sql_exception $erro) {
            error_log('Falha no cadastro de usuário. Código MySQL: ' . $erro->getCode());

            if ($erro->getCode() === 1062) {
                $mensagem = 'Este e-mail já está cadastrado. Use outro e-mail.';
            } elseif (in_array($erro->getCode(), [1054, 1146, 1364], true)) {
                $mensagem = 'O banco de dados está desatualizado. Execute novamente o arquivo database/schema.sql e tente de novo.';
            } else {
                $mensagem = 'Não foi possível cadastrar o usuário. Verifique a conexão com o banco e o arquivo database/schema.sql.';
            }
            $tipo_mensagem = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Better Organized - Acesso</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css">
  <link rel="stylesheet" href="LoginCadastro/style.css">
</head>
<body>
<?php if ($mensagem !== '') : ?>
  <p class="mensagem <?= htmlspecialchars($tipo_mensagem, ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
  </p>
<?php endif; ?>
<div class="container" id="container">
  <div class="form-container sign-up-container">
    <form method="POST">
      <h1>Criar conta</h1>
      <span>Insira os dados abaixo</span>
      <input type="text" placeholder="Nome" name="name" required>
      <input type="email" placeholder="E-mail" name="email2" required>
      <input type="password" placeholder="Senha (mínimo 8 caracteres)" name="senha2" minlength="8" required>
      <p>O ID será gerado automaticamente.</p>
      <label><input type="radio" name="escolha" value="1" required> Professor</label>
      <label><input type="radio" name="escolha" value="0"> Suporte</label>
      <button type="submit" name="sub2">Cadastrar</button>
    </form>
  </div>
  <div class="form-container sign-in-container">
    <form method="POST">
      <h1>Entrar</h1>
      <span>Insira seus dados</span>
      <label for="email">E-mail</label>
      <input id="email" placeholder="E-mail" type="email" name="email" required>
      <label for="senha">Senha</label>
      <input id="senha" placeholder="Senha" type="password" name="senha" required>
      <button type="submit" name="sub">Login</button>
    </form>
  </div>
  <div class="overlay-container">
    <div class="overlay">
      <div class="overlay-panel overlay-left">
        <h1>Bem-vindo de volta</h1>
        <p>Entre com suas informações pessoais.</p>
        <button class="ghost" id="signIn" type="button">Entrar</button>
      </div>
      <div class="overlay-panel overlay-right">
        <h1>Olá</h1>
        <p>Insira seus dados e comece sua jornada.</p>
        <button class="ghost" id="signUp" type="button">Cadastrar</button>
      </div>
    </div>
  </div>
</div>
<script src="LoginCadastro/script.js"></script>
</body>
</html>
