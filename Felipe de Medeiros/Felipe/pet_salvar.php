<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('pets.php');
}
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);
$dados = [
    'nome' => trim((string) ($_POST['nome'] ?? '')),
    'tutor_id' => (int) ($_POST['tutor_id'] ?? 0),
    'especie' => trim((string) ($_POST['especie'] ?? '')),
    'raca' => trim((string) ($_POST['raca'] ?? '')),
    'sexo' => trim((string) ($_POST['sexo'] ?? '')),
    'data_nascimento' => trim((string) ($_POST['data_nascimento'] ?? '')),
    'observacoes' => trim((string) ($_POST['observacoes'] ?? ''))
];

$stmt = $pdo->prepare('SELECT id FROM tutores WHERE id = :id');
$stmt->execute(['id' => $dados['tutor_id']]);
if ($dados['nome'] === '' || $dados['especie'] === '' || !$stmt->fetch()) {
    salvar_dados_antigos($dados);
    mensagem('erro', 'Informe o nome, a espécie e selecione um tutor válido.');
    redirecionar($id ? 'pet_form.php?id=' . $id : 'pet_form.php');
}

$dataNascimento = $dados['data_nascimento'] !== '' ? $dados['data_nascimento'] : null;
if ($id > 0) {
    $stmt = $pdo->prepare(
        'UPDATE pets SET nome = :nome, tutor_id = :tutor_id, especie = :especie, raca = :raca,
         sexo = :sexo, data_nascimento = :data_nascimento, observacoes = :observacoes WHERE id = :id'
    );
    $stmt->execute([
        'nome' => $dados['nome'], 'tutor_id' => $dados['tutor_id'], 'especie' => $dados['especie'],
        'raca' => $dados['raca'] ?: null, 'sexo' => $dados['sexo'] ?: null,
        'data_nascimento' => $dataNascimento, 'observacoes' => $dados['observacoes'] ?: null, 'id' => $id
    ]);
    mensagem('sucesso', 'Dados do pet atualizados.');
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO pets (nome, tutor_id, especie, raca, sexo, data_nascimento, observacoes)
         VALUES (:nome, :tutor_id, :especie, :raca, :sexo, :data_nascimento, :observacoes)'
    );
    $stmt->execute([
        'nome' => $dados['nome'], 'tutor_id' => $dados['tutor_id'], 'especie' => $dados['especie'],
        'raca' => $dados['raca'] ?: null, 'sexo' => $dados['sexo'] ?: null,
        'data_nascimento' => $dataNascimento, 'observacoes' => $dados['observacoes'] ?: null
    ]);
    mensagem('sucesso', 'Pet cadastrado e vinculado ao tutor.');
}

redirecionar('pets.php');
