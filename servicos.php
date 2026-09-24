<?php
    declare(strict_types=1);
    require_once	__DIR__	.	'/conexao.php';
    require_once	__DIR__	.	'/auth_check.php';
    if	(($_SESSION['usuario_role']	??	'')	!==	'admin')	{
        $_SESSION['flash']	=	'Acesso	restrito	a	administradores.';
        header('Location:	index.php');	exit;
    }
    $servicos	=	$pdo->query("SELECT	id,	nome,	duracao_min,	preco	FROM	servicos	ORDER	BY	nome")->fetchAll();
    ?>
    <!DOCTYPE	html>
    <html	lang="pt-BR">
    <head>
        <meta	charset="UTF-8">
        <title>GlamTime	—	Serviços</title>
        <link	href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"	rel="stylesheet">
    </head>
    <body	class="bg-light">
        <?php	require_once	__DIR__	.	'/navbar.php';	?>
        <main	class="container">
            <h4	class="fw-bold">Serviços</h4>
            <table	class="table	table-hover	bg-white	shadow-sm">
                <thead><tr><th>Nome</th><th>Duração	(min)</th><th>Preço</th></tr></thead>
                <tbody><?php	foreach	($servicos	as	$s):	?><tr>
                    <td><?=	htmlspecialchars($s['nome'])	?></td>
                    <td><?=	(int)	$s['duracao_min']	?></td>
                    <td>R$	<?=	number_format((float)	$s['preco'],	2,	',',	'.')	?></td>
                </tr><?php	endforeach;	?></tbody>
            </table>
        </main>
    </body>
    </html>