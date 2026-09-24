<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    header('Location: clientes.php'); exit;
}

$id       = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
$nome     = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email    = trim($_POST['email'] ?? '');

$erros = [];
if ($nome === '') {
    $erros[] = 'Informe o nome.';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'E-mail inválido.';
}

if ($erros) {
    $_SESSION['erro_form'] = implode(' ', $erros);
    header('Location: cliente_form.php' . ($id ? "?id={$id}" : ''));
    exit;
}

$telefone = $telefone !== '' ? $telefone : null;
$email    = $email !== '' ? $email : null;

try {
    if ($id) {
        $stmt = $pdo->prepare(
            "UPDATE clientes SET nome = :nome, telefone = :telefone, email = :email WHERE id = :id"
        );
        $stmt->execute([':nome' => $nome, ':telefone' => $telefone, ':email' => $email, ':id' => $id]);
        $_SESSION['flash'] = 'Cliente atualizado.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO clientes (nome, telefone, email) VALUES (:nome, :telefone, :email)"
        );
        $stmt->execute([':nome' => $nome, ':telefone' => $telefone, ':email' => $email]);
        $_SESSION['flash'] = 'Cliente criado.';
    }
} catch (PDOException $e) {
    // e-mail duplicado (UNIQUE KEY uq_cliente_email) cai aqui
    $_SESSION['erro_form'] = str_contains($e->getMessage(), 'uq_cliente_email')
        ? 'Já existe um cliente com esse e-mail.'
        : 'Erro ao salvar cliente.';
    header('Location: cliente_form.php' . ($id ? "?id={$id}" : ''));
    exit;
}

header('Location: clientes.php');
exit;