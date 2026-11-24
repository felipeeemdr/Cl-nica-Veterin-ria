<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$tutor = ['nome' => '', 'cpf' => '', 'telefone' => '', 'email' => '', 'endereco' => ''];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM tutores WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        mensagem('erro', 'Tutor não encontrado.');
        redirecionar('tutores.php');
    }
    $tutor = [
        'nome' => $registro['nome'],
        'cpf' => descriptografar_cpf($registro['cpf_criptografado']),
        'telefone' => $registro['telefone'],
        'email' => $registro['email'],
        'endereco' => $registro['endereco']
    ];
}

$antigos = dados_antigos();
if ($antigos) {
    $tutor = array_merge($tutor, $antigos);
}

$titulo = $id ? 'Editar tutor' : 'Novo tutor';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titulo) ?> - Clínica Veterinária</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topo">
    <div class="topo-conteudo">
        <div class="marca">Clínica Veterinária</div>
        <nav class="menu">
            <a href="index.php">Início</a>
            <a href="tutores.php">Tutores</a>
            <a href="pets.php">Pets</a>
            <a href="agendamentos.php">Agendamentos</a>
        </nav>
        <div class="usuario-topo">
            <span><?= h($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
            <a class="sair" href="logout.php">Sair</a>
        </div>
    </div>
</header>
<main class="container">
<?php mostrar_mensagem(); ?>
<?php

?>
<section class="cabecalho-pagina">
    <h1><?= $id ? 'Editar tutor' : 'Novo tutor' ?></h1>
</section>

<section class="bloco formulario-bloco">
    <form method="post" action="tutor_salvar.php">
        <input type="hidden" name="csrf_token" value="<?= h(token_csrf()) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="campo">
            <label for="nome">Nome completo *</label>
            <input type="text" id="nome" name="nome" maxlength="120" value="<?= h($tutor['nome']) ?>" required>
        </div>
        <div class="campo">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" value="<?= h(formatar_cpf($tutor['cpf'])) ?>" required>
        </div>
        <div class="campo">
            <label for="telefone">Telefone *</label>
            <input type="text" id="telefone" name="telefone" maxlength="20" value="<?= h($tutor['telefone']) ?>" required>
        </div>
        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="120" value="<?= h($tutor['email']) ?>">
        </div>
        <div class="campo campo-largo">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" maxlength="180" value="<?= h($tutor['endereco']) ?>">
        </div>

        <div class="acoes-form">
            <button type="submit" class="botao">Salvar tutor</button>
            <a href="tutores.php" class="botao secundario">Cancelar</a>
        </div>
    </form>
    <p class="observacao">* Campos obrigatórios. O CPF é armazenado criptografado no banco de dados.</p>
</section>
<script>
document.getElementById('cpf').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    this.value = v;
});
</script>
</main>
<footer class="rodape">Sistema de gerenciamento da clínica veterinária</footer>
</body>
</html>

