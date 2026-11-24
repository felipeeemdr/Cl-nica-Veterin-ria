<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

$stmt = $pdo->query(
    'SELECT p.id, p.nome, p.especie, p.raca, p.sexo, t.nome AS tutor_nome
     FROM pets p INNER JOIN tutores t ON t.id = p.tutor_id ORDER BY p.nome ASC'
);
$pets = $stmt->fetchAll();

$titulo = 'Pets';
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
<section class="cabecalho-pagina linha-titulo">
    <div><h1>Pets</h1></div>
    <a href="pet_form.php" class="botao">Novo pet</a>
</section>
<section class="bloco">
    <div class="tabela-area">
        <table>
            <thead><tr><th>Nome</th><th>Espécie</th><th>Raça</th><th>Sexo</th><th>Tutor</th><th>Ação</th></tr></thead>
            <tbody>
            <?php foreach ($pets as $pet): ?>
                <tr>
                    <td><?= h($pet['nome']) ?></td>
                    <td><?= h($pet['especie']) ?></td>
                    <td><?= h($pet['raca'] ?: '—') ?></td>
                    <td><?= h($pet['sexo'] ?: '—') ?></td>
                    <td><?= h($pet['tutor_nome']) ?></td>
                    <td>
                        <div class="acoes-tabela">
                            <a href="pet_form.php?id=<?= (int) $pet['id'] ?>">Editar</a>
                            <form method="post" action="pet_excluir.php" onsubmit="return confirm('Excluir este pet? Os agendamentos vinculados também serão excluídos.');">
                                <input type="hidden" name="csrf_token" value="<?= h(token_csrf()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $pet['id'] ?>">
                                <button type="submit" class="botao-link excluir-link">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$pets): ?><tr><td colspan="6" class="vazio">Nenhum pet cadastrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
</main>
<footer class="rodape">Sistema de gerenciamento da clínica veterinária</footer>
</body>
</html>

