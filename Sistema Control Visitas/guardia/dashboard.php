<?php
require_once __DIR__.'/../funciones.php'; requireRole('Guardia','..');
$activas=contar($conn,"SELECT COUNT(*) FROM visitas WHERE estado_visita='Activa'");
$disponibles=contar($conn,"SELECT COUNT(*) FROM codigos_qr WHERE estado='Disponible' AND activo=1");
$hoy=contar($conn,"SELECT COUNT(*) FROM visitas WHERE DATE(fecha_registro)=CURDATE()");
$titulo='Panel de Guardia';$base='..';include __DIR__.'/../header.php'; ?>
<div class="hero"><div><span class="eyebrow">PANEL DE GUARDIA</span><h1>Control de acceso de visitantes</h1><p>Registra visitantes, asigna un QR reutilizable, escanea únicamente la entrada y marca la salida desde “Personas en el instituto”.</p></div><img class="hero-logo" src="../assets/logo.png" alt="Logo"></div>
<div class="stats"><div class="stat"><span>En el instituto</span><strong><?=$activas?></strong></div><div class="stat"><span>QR disponibles</span><strong><?=$disponibles?></strong></div><div class="stat"><span>Total hoy</span><strong><?=$hoy?></strong></div></div>
<div class="grid actions"><a class="action-card" href="registrar_visita.php"><b>1</b><div><h3>Registrar visitante</h3><p>Busca por DNI y asigna uno de los QR disponibles.</p></div></a><a class="action-card" href="scanner_qr.php"><b>2</b><div><h3>Escanear entrada</h3><p>El lector únicamente registra el ingreso del visitante.</p></div></a><a class="action-card" href="visitas_activas.php"><b>3</b><div><h3>Personas en el instituto</h3><p>Consulta quién está dentro y usa “Ya salió” para liberar su QR.</p></div></a><a class="action-card" href="historial.php"><b>4</b><div><h3>Historial</h3><p>Consulta visitas anteriores.</p></div></a></div>
<?php include __DIR__.'/../footer.php'; ?>
