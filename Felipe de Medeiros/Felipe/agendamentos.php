<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

$editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT) ?: 0;
$agendamentoEdicao = null;
if ($editarId > 0) {
    $stmt = $pdo->prepare('SELECT id, data_agendamento FROM agendamentos WHERE id = :id');
    $stmt->execute(['id' => $editarId]);
    $agendamentoEdicao = $stmt->fetch();
    if (!$agendamentoEdicao) {
        mensagem('erro', 'Agendamento não encontrado.');
        redirecionar('agendamentos.php');
    }
}

$pets = $pdo->query(
    'SELECT p.id, p.nome AS pet_nome, t.nome AS tutor_nome
     FROM pets p INNER JOIN tutores t ON t.id = p.tutor_id ORDER BY t.nome, p.nome'
)->fetchAll();

$agendamentos = $pdo->query(
    'SELECT a.id, a.data_agendamento, a.motivo, a.status,
            p.nome AS pet_nome, p.especie, p.raca, p.sexo, p.data_nascimento, p.observacoes AS pet_observacoes,
            t.nome AS tutor_nome, t.cpf_criptografado, t.telefone AS tutor_telefone,
            t.email AS tutor_email, t.endereco AS tutor_endereco
     FROM agendamentos a
     INNER JOIN pets p ON p.id = a.pet_id
     INNER JOIN tutores t ON t.id = p.tutor_id
     ORDER BY a.data_agendamento ASC'
)->fetchAll();

$titulo = 'Agendamentos';
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
    <h1>Agendamentos</h1>
</section>

<?php if ($agendamentoEdicao): ?>
<section class="bloco formulario-bloco">
    <h2>Alterar data do agendamento #<?= (int) $agendamentoEdicao['id'] ?></h2>
    <form method="post" action="agendamento_salvar.php" class="formulario-data">
        <input type="hidden" name="csrf_token" value="<?= h(token_csrf()) ?>">
        <input type="hidden" name="id" value="<?= (int) $agendamentoEdicao['id'] ?>">
        <div class="campo">
            <label for="data_agendamento">Nova data e hora</label>
            <input type="datetime-local" id="data_agendamento" name="data_agendamento" value="<?= h(date('Y-m-d\TH:i', strtotime($agendamentoEdicao['data_agendamento']))) ?>" required>
        </div>
        <div class="acoes-form">
            <button type="submit" class="botao">Atualizar data</button>
            <a href="agendamentos.php" class="botao secundario">Cancelar</a>
        </div>
    </form>
</section>
<?php elseif ($pets): ?>
<section class="bloco formulario-bloco">
    <h2>Novo agendamento</h2>
    <form method="post" action="agendamento_salvar.php">
        <input type="hidden" name="csrf_token" value="<?= h(token_csrf()) ?>">
        <input type="hidden" name="id" value="0">
        <div class="campo campo-largo">
            <label for="pet_id">Pet / Tutor *</label>
            <select name="pet_id" id="pet_id" required>
                <option value="">Selecione</option>
                <?php foreach ($pets as $pet): ?>
                    <option value="<?= (int) $pet['id'] ?>"><?= h($pet['pet_nome'] . ' — ' . $pet['tutor_nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="data_agendamento_novo">Data e hora *</label>
            <input type="datetime-local" id="data_agendamento_novo" name="data_agendamento" required>
        </div>
        <div class="campo">
            <label for="motivo">Motivo do atendimento</label>
            <input type="text" id="motivo" name="motivo" maxlength="180">
        </div>
        <div class="acoes-form">
            <button type="submit" class="botao">Cadastrar agendamento</button>
        </div>
    </form>
</section>
<?php else: ?>
<section class="bloco"><p class="vazio">Cadastre pelo menos um pet antes de criar um agendamento.</p><a class="botao" href="pet_form.php">Cadastrar pet</a></section>
<?php endif; ?>

<section class="bloco">
    <div class="bloco-titulo"><h2>Agenda cadastrada</h2><span><?= count($agendamentos) ?> registro(s)</span></div>
    <div class="tabela-area">
        <table>
            <thead><tr><th>Data</th><th>Dados do pet</th><th>Dados do tutor</th><th>Motivo</th><th>Status</th><th>Ação</th></tr></thead>
            <tbody>
            <?php foreach ($agendamentos as $item): ?>
                <tr>
                    <td><?= h(formatar_data_hora($item['data_agendamento'])) ?></td>
                    <td>
                        <strong><?= h($item['pet_nome']) ?></strong><br>
                        Espécie: <?= h($item['especie']) ?><br>
                        Raça: <?= h($item['raca'] ?: 'Não informada') ?><br>
                        Sexo: <?= h($item['sexo'] ?: 'Não informado') ?><br>
                        Nascimento: <?= h(formatar_data($item['data_nascimento'])) ?><br>
                        Observações: <?= h($item['pet_observacoes'] ?: '—') ?>
                    </td>
                    <td>
                        <strong><?= h($item['tutor_nome']) ?></strong><br>
                        CPF: <?= h(formatar_cpf(descriptografar_cpf($item['cpf_criptografado']))) ?><br>
                        Telefone: <?= h($item['tutor_telefone']) ?><br>
                        E-mail: <?= h($item['tutor_email'] ?: '—') ?><br>
                        Endereço: <?= h($item['tutor_endereco'] ?: '—') ?>
                    </td>
                    <td><?= h($item['motivo'] ?: '—') ?></td>
                    <td>
                        <span class="status <?= $item['status'] === 'Pronto' ? 'status-pronto' : 'status-agendado' ?>">
                            <?= h($item['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="acoes-tabela">
                            <a href="agendamentos.php?editar=<?= (int) $item['id'] ?>">Alterar data</a>
                            <form method="post" action="agendamento_status.php">
                                <input type="hidden" name="csrf_token" value="<?= h(token_csrf()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <?php if ($item['status'] === 'Pronto'): ?>
                                    <input type="hidden" name="status" value="Agendado">
                                    <button type="submit" class="botao-link">Reabrir</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="Pronto">
                                    <button type="submit" class="botao-link pronto-link">Marcar como pronto</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$agendamentos): ?><tr><td colspan="6" class="vazio">Nenhum agendamento cadastrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
</main>
<footer class="rodape">Sistema de gerenciamento da clínica veterinária</footer>
</body>
</html>

