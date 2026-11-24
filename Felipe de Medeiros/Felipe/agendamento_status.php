<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('agendamentos.php');
}
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');

if ($id <= 0 || !in_array($status, ['Agendado', 'Pronto'], true)) {
    mensagem('erro', 'Não foi possível atualizar o serviço.');
    redirecionar('agendamentos.php');
}

$stmt = $pdo->prepare('UPDATE agendamentos SET status = :status WHERE id = :id');
$stmt->execute(['status' => $status, 'id' => $id]);

if ($stmt->rowCount() > 0) {
    mensagem('sucesso', $status === 'Pronto' ? 'Serviço marcado como pronto.' : 'Agendamento reaberto.');
} else {
    $stmt = $pdo->prepare('SELECT id FROM agendamentos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    if ($stmt->fetch()) {
        mensagem('sucesso', $status === 'Pronto' ? 'Serviço marcado como pronto.' : 'Agendamento reaberto.');
    } else {
        mensagem('erro', 'Agendamento não encontrado.');
    }
}

redirecionar('agendamentos.php');
