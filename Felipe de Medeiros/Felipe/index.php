<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

$totalTutores = (int) $pdo->query('SELECT COUNT(*) FROM tutores')->fetchColumn();
$totalPets = (int) $pdo->query('SELECT COUNT(*) FROM pets')->fetchColumn();
$totalAgendamentos = (int) $pdo->query('SELECT COUNT(*) FROM agendamentos')->fetchColumn();

$stmt = $pdo->query(
    "SELECT a.id, a.data_agendamento, a.status, p.nome AS pet_nome, t.nome AS tutor_nome
     FROM agendamentos a
     INNER JOIN pets p ON p.id = a.pet_id
     INNER JOIN tutores t ON t.id = p.tutor_id
     WHERE a.data_agendamento >= NOW()
     ORDER BY a.data_agendamento ASC LIMIT 5"
);
$proximos = $stmt->fetchAll();

$titulo = 'Início';
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
    <div>
        <h1>Olá, <?= h($_SESSION['usuario_nome'] ?? 'usuário') ?>.</h1>
    </div>
</section>

<section class="resumos">
    <div class="resumo"><span>Tutores cadastrados</span><strong><?= $totalTutores ?></strong></div>
    <div class="resumo"><span>Pets cadastrados</span><strong><?= $totalPets ?></strong></div>
    <div class="resumo"><span>Agendamentos</span><strong><?= $totalAgendamentos ?></strong></div>
</section>

<section class="atalhos">
    <a href="tutores.php" class="atalho"><strong>Gerenciar tutores</strong></a>
    <a href="pets.php" class="atalho"><strong>Gerenciar pets</strong></a>
    <a href="agendamentos.php" class="atalho"><strong>Gerenciar agendamentos</strong></a>
</section>

<section class="bloco">
    <div class="bloco-titulo"><h2>Próximos agendamentos</h2><a href="agendamentos.php">Ver todos</a></div>
    <?php if (!$proximos): ?>
        <p class="vazio">Não há agendamentos futuros.</p>
    <?php else: ?>
        <div class="tabela-area">
            <table>
                <thead><tr><th>Data e hora</th><th>Pet</th><th>Tutor</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($proximos as $item): ?>
                    <tr>
                        <td><?= h(formatar_data_hora($item['data_agendamento'])) ?></td>
                        <td><?= h($item['pet_nome']) ?></td>
                        <td><?= h($item['tutor_nome']) ?></td>
                        <td><?= h($item['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
</main>
<footer class="rodape">Sistema de gerenciamento da clínica veterinária</footer>
</body>
</html>

