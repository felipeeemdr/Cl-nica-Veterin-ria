<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$pet = ['nome' => '', 'tutor_id' => '', 'especie' => 'Cachorro', 'raca' => '', 'sexo' => '', 'data_nascimento' => '', 'observacoes' => ''];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM pets WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        mensagem('erro', 'Pet não encontrado.');
        redirecionar('pets.php');
    }
    $pet = $registro;
}

$antigos = dados_antigos();
if ($antigos) {
    $pet = array_merge($pet, $antigos);
}

$tutores = $pdo->query('SELECT id, nome FROM tutores ORDER BY nome')->fetchAll();
$titulo = $id ? 'Editar pet' : 'Novo pet';
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
    <h1><?= $id ? 'Editar pet' : 'Novo pet' ?></h1>
</section>
<section class="bloco formulario-bloco">
    <?php if (!$tutores): ?>
        <div class="mensagem aviso">Cadastre um tutor antes de cadastrar um pet.</div>
        <a href="tutor_form.php" class="botao">Cadastrar tutor</a>
    <?php else: ?>
    <form method="post" action="pet_salvar.php">
        <input type="hidden" name="csrf_token" value="<?= h(token_csrf()) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="campo">
            <label for="nome">Nome do pet *</label>
            <input type="text" id="nome" name="nome" maxlength="100" value="<?= h($pet['nome']) ?>" required>
        </div>
        <div class="campo">
            <label for="tutor_id">Tutor *</label>
            <select id="tutor_id" name="tutor_id" required>
                <option value="">Selecione um tutor</option>
                <?php foreach ($tutores as $tutor): ?>
                    <option value="<?= (int) $tutor['id'] ?>" <?= (string) $pet['tutor_id'] === (string) $tutor['id'] ? 'selected' : '' ?>><?= h($tutor['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="especie">Espécie *</label>
            <select id="especie" name="especie" required>
                <?php foreach (['Cachorro', 'Gato', 'Ave', 'Roedor', 'Réptil', 'Outro'] as $especie): ?>
                    <option value="<?= h($especie) ?>" <?= $pet['especie'] === $especie ? 'selected' : '' ?>><?= h($especie) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="raca">Raça</label>
            <input type="text" id="raca" name="raca" maxlength="80" value="<?= h($pet['raca']) ?>">
        </div>
        <div class="campo">
            <label for="sexo">Sexo</label>
            <select id="sexo" name="sexo">
                <option value="">Não informado</option>
                <option value="Macho" <?= $pet['sexo'] === 'Macho' ? 'selected' : '' ?>>Macho</option>
                <option value="Fêmea" <?= $pet['sexo'] === 'Fêmea' ? 'selected' : '' ?>>Fêmea</option>
            </select>
        </div>
        <div class="campo">
            <label for="data_nascimento">Data de nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento" value="<?= h($pet['data_nascimento']) ?>">
        </div>
        <div class="campo campo-largo">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3"><?= h($pet['observacoes']) ?></textarea>
        </div>
        <div class="acoes-form">
            <button type="submit" class="botao">Salvar pet</button>
            <a href="pets.php" class="botao secundario">Cancelar</a>
        </div>
    </form>
    <?php endif; ?>
</section>
</main>
<footer class="rodape">Sistema de gerenciamento da clínica veterinária</footer>
</body>
</html>

