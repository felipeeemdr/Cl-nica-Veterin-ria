<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('agendamentos.php');
}
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);
$data = trim((string) ($_POST['data_agendamento'] ?? ''));

if ($data === '') {
    mensagem('erro', 'Informe a data e a hora do agendamento.');
    redirecionar('agendamentos.php');
}
$dataMysql = str_replace('T', ' ', $data);
if (strlen($dataMysql) === 16) {
    $dataMysql .= ':00';
}
$timestamp = strtotime($dataMysql);
if ($timestamp === false) {
    mensagem('erro', 'A data informada não é válida.');
    redirecionar('agendamentos.php');
}
$dataMysql = date('Y-m-d H:i:s', $timestamp);

if ($id > 0) {
    $stmt = $pdo->prepare('UPDATE agendamentos SET data_agendamento = :data WHERE id = :id');
    $stmt->execute(['data' => $dataMysql, 'id' => $id]);
    mensagem('sucesso', 'Data do agendamento atualizada.');
    redirecionar('agendamentos.php');
}

$petId = (int) ($_POST['pet_id'] ?? 0);
$stmt = $pdo->prepare('SELECT id FROM pets WHERE id = :id');
$stmt->execute(['id' => $petId]);
if (!$stmt->fetch()) {
    mensagem('erro', 'Selecione um pet cadastrado.');
    redirecionar('agendamentos.php');
}

$motivo = trim((string) ($_POST['motivo'] ?? ''));
$stmt = $pdo->prepare(
    "INSERT INTO agendamentos (pet_id, data_agendamento, motivo, status)
     VALUES (:pet_id, :data, :motivo, 'Agendado')"
);
$stmt->execute(['pet_id' => $petId, 'data' => $dataMysql, 'motivo' => $motivo ?: null]);
mensagem('sucesso', 'Agendamento cadastrado.');
redirecionar('agendamentos.php');
