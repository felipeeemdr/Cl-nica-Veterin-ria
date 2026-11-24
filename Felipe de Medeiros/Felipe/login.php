<?php
require_once __DIR__ . '/funcoes.php';

if (!empty($_SESSION['usuario_id'])) {
    redirecionar('index.php');
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    $stmt = $pdo->prepare('SELECT id, nome, email, senha_hash FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['ultima_atividade'] = time();
        redirecionar('index.php');
    }

    $erro = 'E-mail ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Clínica Veterinária</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="pagina-login">
<div class="login-caixa">
    <h1>Clínica Veterinária</h1>

    <?php mostrar_mensagem(); ?>
    <?php if ($erro !== ''): ?>
        <div class="mensagem erro"><?= h($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= h(token_csrf()) ?>">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= h($email) ?>" required autofocus>

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit" class="botao largura-total">Entrar</button>
    </form>

    <div class="acesso-teste">
        <strong>Acesso inicial para teste</strong><br>
        E-mail: admin@clinica.com<br>
        Senha: Senai@123
    </div>
</div>
</body>
</html>
