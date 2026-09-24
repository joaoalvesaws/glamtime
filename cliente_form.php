<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$cliente = ['nome' => '', 'telefone' => '', 'email' => ''];

if ($id) {
    $stmt = $pdo->prepare("SELECT id, nome, telefone, email FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch();
    if (!$cliente) {
        $_SESSION['flash'] = 'Cliente não encontrado.';
        header('Location: clientes.php');
        exit;
    }
}

$csrf = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));
$erro = $_SESSION['erro_form'] ?? null;
unset($_SESSION['erro_form']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — <?= $id ? 'Editar' : 'Novo' ?> Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container" style="max-width:480px">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="fw-bold mb-3"><?= $id ? 'Editar cliente' : 'Novo cliente' ?></h5>
        <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <form method="post" action="cliente_salvar.php">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required maxlength="80" value="<?= htmlspecialchars($cliente['nome']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" class="form-control" maxlength="20" value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" maxlength="120" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
          </div>
          <button class="btn btn-primary w-100 mb-2">Salvar</button>
          <a href="clientes.php" class="btn btn-outline-secondary w-100">Cancelar</a>
        </form>
      </div>
    </div>
  </main>
</body>
</html>