<?php
require_once __DIR__.'/../funciones.php'; requireRole('Guardia','..');
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['salida'])){
    $id=(int)$_POST['salida'];
    $res=registrarSalida($conn,$id);
    flash($res['ok']?'success':'danger',$res['mensaje']);
    redirect('visitas_activas.php');
}
$r=mysqli_query($conn,"SELECT v.*,vi.nombre_completo,vi.numero_identidad FROM visitas v JOIN visitantes vi ON vi.id_visitante=v.id_visitante WHERE v.estado_visita='Activa' ORDER BY v.hora_entrada DESC");
$lista=$r->fetch_all(MYSQLI_ASSOC);
$titulo='Personas en el instituto';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1>Personas en el instituto</h1><p>Desde aquí el vigilante registra la salida. Al salir, el QR se libera y puede entregarse a otra persona.</p></div><a class="btn ghost" href="dashboard.php">← Volver</a></div>
<?php if(!$lista):?><div class="empty card">✅ No hay visitantes dentro del instituto actualmente.</div><?php else:?><div class="table-card"><table><thead><tr><th>Visitante</th><th>DNI</th><th>Destino</th><th>QR asignado</th><th>Entrada</th><th>Acción</th></tr></thead><tbody><?php foreach($lista as $v):?><tr><td><strong><?=e($v['nombre_completo'])?></strong><br><small><?=e($v['persona_a_visitar'])?></small></td><td><?=e($v['numero_identidad'])?></td><td><?=e($v['area_destino'])?></td><td><code><?=e($v['codigo_qr'])?></code></td><td><?=e($v['hora_entrada'])?></td><td><form method="post" onsubmit="return confirm('¿Confirmar que <?=e($v['nombre_completo'])?> ya salió del instituto? El QR volverá a estar disponible.');"><input type="hidden" name="salida" value="<?=$v['id_visita']?>"><button class="btn danger small" type="submit">✓ Ya salió</button></form></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
<?php include __DIR__.'/../footer.php'; ?>
