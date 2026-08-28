<?php require_once __DIR__.'/funciones.php'; $flash=takeFlash(); ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Control de Visitas') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e($base ?? '.') ?>/css/style.css"></head><body>
<header class="topbar"><a class="brand" href="<?= e($base ?? '.') ?>/index.php"><img src="<?= e($base ?? '.') ?>/assets/logo.png" alt="Logo"><span><b>Control de Visitas</b><small>C.E.M.G. Técnico “Dr. Jorge Fidel Durón”</small></span></a>
<?php if(isLoggedIn()): ?><nav><span class="user-chip"><?= e($_SESSION['user_name']) ?> · <?= e($_SESSION['user_rol']) ?></span><a class="btn small ghost" href="<?= e($base ?? '.') ?>/logout.php">Cerrar sesión</a></nav><?php endif; ?></header>
<main class="container">
<?php if($flash): ?><div class="alert <?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div><?php endif; ?>
