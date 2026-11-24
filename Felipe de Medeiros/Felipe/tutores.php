<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

$busca = trim((string) ($_GET['busca'] ?? ''));
$sql = 'SELECT id, nome, cpf_criptografado, telefone, email FROM tutores';
$params = [];
if ($busca !== '') {
    $sql .= ' WHERE (nome LIKE :busca_nome OR telefone LIKE :busca_telefone OR email LIKE :busca_email';
    $params['busca_nome'] = '%' . $busca . '%';
    $params['busca_telefone'] = '%' . $busca . '%';
    $params['busca_email'] = '%' . $busca . '%';
    $cpfBusca = somente_numeros($busca);
    if (strlen($cpfBusca) === 11) {
        $sql .= ' OR cpf_hash = :cpf_hash';
        $params['cpf_hash'] = hash_cpf($cpfBusca);
    }
    $sql .= ')';
}
$sql .= ' ORDER BY nome ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tutores = $stmt->fetchAll();

$titulo = 'Tutores';
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
    <div><h1>Tutores</h1></div>
    <a href="tutor_form.php" class="botao">Novo tutor</a>
</section>

<section class="bloco">
    <form method="get" class="busca-form">
        <input type="search" name="busca" placeholder="Buscar por nome, telefone, e-mail ou CPF" value="<?= h($busca) ?>">
        <button class="botao" type="submit">Buscar</button>
        <?php if ($busca !== ''): ?><a class="botao secundario" href="tutores.php">Limpar</a><?php endif; ?>
    </form>

    <div class="tabela-area">
        <table>
            <thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>E-mail</th><th>Ação</th></tr></thead>
            <tbody>
            <?php foreach ($tutores as $tutor): ?>
                <tr>
                    <td><?= h($tutor['nome']) ?></td>
                    <td><?= h(formatar_cpf(descriptografar_cpf($tutor['cpf_criptografado']))) ?></td>
                    <td><?= h($tutor['telefone']) ?></td>
                    <td><?= h($tutor['email'] ?: '—') ?></td>
                    <td><a href="tutor_form.php?id=<?= (int) $tutor['id'] ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tutores): ?><tr><td colspan="5" class="vazio">Nenhum tutor encontrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="total-registros"><?= count($tutores) ?> registro(s) encontrado(s).</p>
</section>
</main>
<footer class="rodape">Sistema de gerenciamento da clínica veterinária</footer>
</body>
</html>

