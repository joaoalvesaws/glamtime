<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    header('Location: clientes.php'); exit;
}

$id = (int) ($_POST['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $_SESSION['flash'] = $stmt->rowCount() ? 'Cliente excluído.' : 'Cliente não encontrado.';
} catch (PDOException $e) {
    // FK fk_ag_cliente impede excluir cliente com agendamentos vinculados
    $_SESSION['flash'] = 'Não é possível excluir: este cliente tem agendamentos vinculados.';
}

header('Location: clientes.php');
exit;