<?php
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    header('Location: agendamentos.php'); exit;
}

$clienteId = (int) ($_POST['cliente_id'] ?? 0);
$servicoId = (int) ($_POST['servico_id'] ?? 0);
$dataHora  = $_POST['data_hora'] ?? '';

if (!$clienteId || !$servicoId || $dataHora === '' || strtotime($dataHora) === false) {
    $_SESSION['flash'] = 'Dados inválidos.';
    header('Location: agendamento_form.php'); exit;
}

// Regra de negócio: bloqueio de conflito de horário (sobreposição de intervalos)
$pdo->beginTransaction();
try {
    require_once	__DIR__	.	'/src/AgendamentoDAO.php';
    $dao	=	new	AgendamentoDAO($pdo);

    $durStmt	=	$pdo->prepare("SELECT	duracao_min	FROM	servicos	WHERE	id	=	:id");
    $durStmt->execute([':id'	=>	$servicoId]);
    $duracao	=	(int)	$durStmt->fetchColumn();
    $fim	=	date('Y-m-d	H:i:s',	strtotime($dataHora)	+	$duracao	*	60);
if	($dao->verificarConflito($dataHora,	$fim))	{
    throw	new	RuntimeException('Horário	conflita	com	outro	agendamento	ativo.');
}

    $ins = $pdo->prepare("INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status) VALUES (:c, :s, :d, 'agendado')");
    $ins->execute([':c' => $clienteId, ':s' => $servicoId, ':d' => $dataHora]);
    $pdo->commit();
    $_SESSION['flash'] = 'Agendamento criado com sucesso!';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = $e->getMessage();
}
header('Location: agendamentos.php');
exit;