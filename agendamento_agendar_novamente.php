<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/auth_check.php'; // agora exige login 
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT cliente_id, servico_id FROM agendamentos WHERE id = :id");
$stmt->execute([':id' => $id]);
$ag = $stmt->fetch();
if (!$ag) { header('Location: agendamentos.php'); exit; }
$csrf = $_SESSION['csrf'] ?? ($_SESSION['csrf'] = bin2hex(random_bytes(32)));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (($_POST['csrf'] ?? '') !== $csrf) { die('Token inválido.'); }
$novaData = $_POST['data_hora'] ?? '';
$durStmt = $pdo->prepare("SELECT duracao_min FROM servicos WHERE id = :id");
$durStmt->execute([':id' => (int) $ag['servico_id']]);
$dur = (int) $durStmt->fetchColumn();
$fim = date('Y-m-d H:i:s', strtotime($novaData) + $dur * 60);
require_once __DIR__ . '/src/AgendamentoDAO.php';
$dao = new AgendamentoDAO($pdo);
$pdo->beginTransaction();
try {
if ($dao->verificarConflito($novaData, $fim)) {
throw new RuntimeException('Horário conflita com outro agendamento.');
}
$ins = $pdo->prepare("INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status)
VALUES (:c, :s, :d, 'agendado')");
$ins->execute([':c' => (int) $ag['cliente_id'], ':s' => (int) $ag['servico_id'], ':d' => $novaData]);
$upd = $pdo->prepare("UPDATE agendamentos SET status = 'concluido' WHERE id = :id");
$upd->execute([':id' => $id]);
$pdo->commit();
$_SESSION['flash'] = 'Novo agendamento criado a partir do anterior.';
} catch (Exception $e) {
$pdo->rollBack();
$_SESSION['flash'] = $e->getMessage();
}
header('Location: agendamentos.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>GlamTime — Agendar novamente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navbar.php'; ?>
<main class="container" style="max-width:480px">
<div class="card shadow-sm"><div class="card-body">
<h5 class="fw-bold mb-3">Agendar novamente</h5>
<form method="post" onsubmit="return confirm('Criar novo agendamento?')">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
<div class="mb-3">
<label class="form-label">Nova data e hora</label>
<input type="datetime-local" class="form-control" name="data_hora" required>
</div>
<button class="btn btn-primary w-100">Confirmar</button>
<a href="agendamentos.php" class="btn btn-link w-100">Cancelar</a>
</form>
</div></div>
</main>
</body>
</html>