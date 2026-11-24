<?php
require_once __DIR__ . '/funcoes.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('pets.php');
}
validar_csrf();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    mensagem('erro', 'Pet inválido.');
    redirecionar('pets.php');
}

try {
    $stmt = $pdo->prepare('SELECT id FROM pets WHERE id = :id');
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        mensagem('erro', 'Pet não encontrado.');
        redirecionar('pets.php');
    }

    $pdo->beginTransaction();

    // Remove primeiro os agendamentos para respeitar a chave estrangeira.
    $stmt = $pdo->prepare('DELETE FROM agendamentos WHERE pet_id = :id');
    $stmt->execute(['id' => $id]);

    $stmt = $pdo->prepare('DELETE FROM pets WHERE id = :id');
    $stmt->execute(['id' => $id]);

    $pdo->commit();
    mensagem('sucesso', 'Pet excluído. Os agendamentos vinculados também foram removidos.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    mensagem('erro', 'Não foi possível excluir o pet.');
}

redirecionar('pets.php');
