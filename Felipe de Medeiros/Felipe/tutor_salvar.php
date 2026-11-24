<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('tutores.php');
}
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);
$dados = [
    'nome' => trim((string) ($_POST['nome'] ?? '')),
    'cpf' => trim((string) ($_POST['cpf'] ?? '')),
    'telefone' => trim((string) ($_POST['telefone'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'endereco' => trim((string) ($_POST['endereco'] ?? ''))
];
$cpf = somente_numeros($dados['cpf']);

if ($dados['nome'] === '' || strlen($cpf) !== 11 || $dados['telefone'] === '') {
    salvar_dados_antigos($dados);
    mensagem('erro', 'Preencha nome, CPF com 11 números e telefone.');
    redirecionar($id ? 'tutor_form.php?id=' . $id : 'tutor_form.php');
}
if ($dados['email'] !== '' && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
    salvar_dados_antigos($dados);
    mensagem('erro', 'Informe um e-mail válido.');
    redirecionar($id ? 'tutor_form.php?id=' . $id : 'tutor_form.php');
}

$cpfHash = hash_cpf($cpf);
$stmt = $pdo->prepare('SELECT id FROM tutores WHERE cpf_hash = :cpf_hash AND id <> :id LIMIT 1');
$stmt->execute(['cpf_hash' => $cpfHash, 'id' => $id]);
if ($stmt->fetch()) {
    salvar_dados_antigos($dados);
    mensagem('erro', 'Já existe um tutor cadastrado com esse CPF.');
    redirecionar($id ? 'tutor_form.php?id=' . $id : 'tutor_form.php');
}

if ($id > 0) {
    $stmt = $pdo->prepare(
        'UPDATE tutores SET nome = :nome, cpf_criptografado = :cpf, cpf_hash = :cpf_hash,
         telefone = :telefone, email = :email, endereco = :endereco WHERE id = :id'
    );
    $stmt->execute([
        'nome' => $dados['nome'], 'cpf' => criptografar_cpf($cpf), 'cpf_hash' => $cpfHash,
        'telefone' => $dados['telefone'], 'email' => $dados['email'] ?: null,
        'endereco' => $dados['endereco'] ?: null, 'id' => $id
    ]);
    mensagem('sucesso', 'Dados do tutor atualizados.');
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO tutores (nome, cpf_criptografado, cpf_hash, telefone, email, endereco)
         VALUES (:nome, :cpf, :cpf_hash, :telefone, :email, :endereco)'
    );
    $stmt->execute([
        'nome' => $dados['nome'], 'cpf' => criptografar_cpf($cpf), 'cpf_hash' => $cpfHash,
        'telefone' => $dados['telefone'], 'email' => $dados['email'] ?: null,
        'endereco' => $dados['endereco'] ?: null
    ]);
    mensagem('sucesso', 'Tutor cadastrado com sucesso.');
}

redirecionar('tutores.php');
