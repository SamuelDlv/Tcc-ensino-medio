<?php
require __DIR__ . '/../config/database.php';

// --- LOGIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sub'])) {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $stmt = $con->prepare("SELECT * FROM Usuarios WHERE email = ? AND senha = ?");
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $resultado1 = $stmt->get_result();

    if ($resultado1->num_rows == 1) {
        $usuario = $resultado1->fetch_assoc();
        $_SESSION["ID_USER"] = $usuario["ID_USER"];
        $_SESSION["FUNCAO"] = (int)$usuario["funcao"]; // 1 = professor, 0 = suporte
        if ($usuario["funcao"] == 1) {
            header("Location: /professor/inicio.php");
        } else {
            header("Location: /suporte/inicio.php");
        }
        exit();
    } else {
        $erro_login = "Email ou senha inválidos.";
    }
    $stmt->close();
}

// --- CADASTRO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sub2'])) {
    $nome = $_POST['name'];
    $email2 = $_POST['email2'];
    $senha2 = $_POST['senha2'];
    // funcao vem do radio button: 1 = professor, 0 = suporte
    $funcao = $_POST['escolha'];

    // Verifica se já existe um usuário com esse email (ID_USER agora é
    // gerado automaticamente pelo banco, não é mais digitado no formulário)
    $check = $con->prepare("SELECT ID_USER FROM Usuarios WHERE email = ?");
    $check->bind_param("s", $email2);
    $check->execute();
    $existe = $check->get_result();
    $check->close();

    if ($existe->num_rows > 0) {
        $erro_cadastro = "Não é possível cadastrar. Já existe um usuário com esse email.";
    } else {
        $ins = $con->prepare("INSERT INTO Usuarios (email, senha, nome, funcao) VALUES (?, ?, ?, ?)");
        $ins->bind_param("sssi", $email2, $senha2, $nome, $funcao);
        if ($ins->execute()) {
            $sucesso_cadastro = "Usuário cadastrado com sucesso! Faça login ao lado.";
        } else {
            $erro_cadastro = "Erro ao cadastrar: " . $con->error;
        }
        $ins->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BetterOrganized - Login</title>
  <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>

<div class="brand"><span class="dot"></span>BetterOrganized</div>

<div class="container" id="container">
	<div class="form-container sign-up-container">
		<form method="POST">
			<h1>Criar conta</h1>
			<span>Insira os dados abaixo</span>
			<?php if (!empty($erro_cadastro)): ?><p class="msg-erro"><?= htmlspecialchars($erro_cadastro) ?></p><?php endif; ?>
			<?php if (!empty($sucesso_cadastro)): ?><p class="msg-sucesso"><?= htmlspecialchars($sucesso_cadastro) ?></p><?php endif; ?>
			<input type="text" placeholder="Nome" name="name" required/>
			<input type="email" placeholder="Email" name="email2" required/>
			<input type="password" placeholder="Senha" name="senha2" required/>

			<div class="radio-group">
				<label><input type="radio" name="escolha" value="1" required> Professor</label>
				<label><input type="radio" name="escolha" value="0"> Suporte</label>
			</div>

			<button name="sub2">Criar conta</button>
			<div class="mobile-switch">Já tem conta? <button type="button" data-switch="sign-in">Entrar</button></div>
		</form>
	</div>

	<div class="form-container sign-in-container">
		<form method="post" action="/index.php">
			<h1>Entrar</h1>
			<span>Insira os dados abaixo</span>
            <?php if (!empty($erro_login)): ?><p class="msg-erro"><?= htmlspecialchars($erro_login) ?></p><?php endif; ?>
            <label for="email">Email</label>
            <input id="email" placeholder="Email" type="email" name="email" required>

            <label for="senha">Senha</label>
            <input id="senha" placeholder="Senha" type="password" name="senha" required>
            <a href="#">Esqueceu sua senha?</a>
            <button type="submit" name="sub">Entrar</button>
            <div class="mobile-switch">Novo por aqui? <button type="button" data-switch="sign-up">Criar conta</button></div>
        </form>
	</div>

	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-left">
				<h1>Bem-vindo de volta</h1>
				<p>Para continuar, faça login com suas informações pessoais</p>
				<button class="ghost" id="signIn">Entrar</button>
			</div>
			<div class="overlay-panel overlay-right">
				<h1>Olá!</h1>
				<p>Crie sua conta e comece a acompanhar os laboratórios</p>
				<button class="ghost" id="signUp">Criar conta</button>
			</div>
		</div>
	</div>
</div>

<script src="/assets/js/login.js"></script>

</body>
</html>
