<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php';

$lista = $pdo->query("SELECT id, nome, telefone, email FROM clientes ORDER BY nome")->fetchAll();
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$csrf  = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GlamTime — Clientes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php require_once __DIR__ . '/navbar.php'; ?>
  <main class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Clientes</h4>
      <a href="cliente_form.php" class="btn btn-primary btn-sm">+ Novo cliente</a>
    </div>
    <?php if ($flash): ?><div class="alert alert-info"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <table class="table table-hover bg-white shadow-sm align-middle">
      <thead><tr><th>Nome</th><th>Telefone</th><th>E-mail</th><th class="text-end">Ações</th></tr></thead>
      <tbody><?php foreach ($lista as $c): ?><tr>
        <td><?= htmlspecialchars($c['nome']) ?></td>
        <td><?= htmlspecialchars($c['telefone'] ?? '') ?></td>
        <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
        <td class="text-end">
          <a href="cliente_form.php?id=<?= (int) $c['id'] ?>" class="btn btn-outline-primary btn-sm">Editar</a>
          <form method="post" action="cliente_excluir.php" class="d-inline" onsubmit="return confirm('Excluir este cliente?')">
            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button class="btn btn-outline-danger btn-sm">Excluir</button>
          </form>
        </td>
      </tr><?php endforeach; ?></tbody>
    </table>
  </main>
</body>
</html>