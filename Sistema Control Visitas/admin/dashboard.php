<?php
require_once __DIR__.'/../funciones.php'; requireRole('Admin','..');
$guardias=contar($conn,"SELECT COUNT(*) FROM usuarios WHERE rol='Guardia'");
$visitantes=contar($conn,"SELECT COUNT(*) FROM visitantes");
$visitas=contar($conn,"SELECT COUNT(*) FROM visitas");
$qrs=contar($conn,"SELECT COUNT(*) FROM codigos_qr WHERE estado='Disponible' AND activo=1");
$titulo='Administración';$base='..';include __DIR__.'/../header.php'; ?>
<div class="hero"><div><span class="eyebrow">PANEL DE ADMINISTRADOR</span><h1>Administración del sistema</h1><p>Gestiona guardias, visitantes, visitas, códigos QR reutilizables y reportes.</p></div><img class="hero-logo" src="../assets/logo.png" alt="Logo"></div>
<div class="stats"><div class="stat"><span>Guardias</span><strong><?=$guardias?></strong></div><div class="stat"><span>Visitantes</span><strong><?=$visitantes?></strong></div><div class="stat"><span>Visitas</span><strong><?=$visitas?></strong></div><div class="stat"><span>QR disponibles</span><strong><?=$qrs?></strong></div></div>
<div class="grid actions">
<a class="action-card" href="usuarios.php"><b>👤</b><div><h3>Guardias</h3><p>Crear, editar, cambiar contraseñas, activar y eliminar cuentas.</p></div></a>
<a class="action-card" href="visitantes.php"><b>🪪</b><div><h3>Visitantes</h3><p>CRUD de los datos personales registrados.</p></div></a>
<a class="action-card" href="visitas.php"><b>📋</b><div><h3>Visitas</h3><p>Consultar, editar, cancelar o eliminar registros.</p></div></a>
<a class="action-card" href="codigos_qr.php"><b>▦</b><div><h3>Códigos QR</h3><p>Crear, editar, activar, imprimir y administrar QR reutilizables.</p></div></a>
<a class="action-card" href="reportes.php"><b>📊</b><div><h3>Reportes</h3><p>Reportes diarios y generales listos para imprimir.</p></div></a>
<a class="action-card" href="perfil.php"><b>🔐</b><div><h3>Mi cuenta</h3><p>Cambiar usuario o contraseña del administrador.</p></div></a>
</div><?php include __DIR__.'/../footer.php'; ?>
