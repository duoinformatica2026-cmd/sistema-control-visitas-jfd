<?php
require_once __DIR__.'/../funciones.php'; requireRole('Admin','..');
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['accion']??'')==='eliminar'){
    $id=(int)($_POST['id']??0);
    $st=mysqli_prepare($conn,"SELECT COUNT(*) total FROM visitas WHERE id_visitante=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$usos=(int)(mysqli_fetch_assoc(mysqli_stmt_get_result($st))['total']??0);
    if($usos>0){flash('danger','No se puede eliminar este visitante porque forma parte del historial. Puede editar sus datos.');redirect('visitantes.php');}
    $st=mysqli_prepare($conn,"DELETE FROM visitantes WHERE id_visitante=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);flash('success','Visitante eliminado.');redirect('visitantes.php');
}
$q=trim($_GET['q']??'');
if($q!==''){$like='%'.$q.'%';$st=mysqli_prepare($conn,"SELECT * FROM visitantes WHERE numero_identidad LIKE ? OR nombre_completo LIKE ? ORDER BY nombre_completo");mysqli_stmt_bind_param($st,'ss',$like,$like);mysqli_stmt_execute($st);$lista=mysqli_stmt_get_result($st)->fetch_all(MYSQLI_ASSOC);}else{$lista=mysqli_query($conn,"SELECT * FROM visitantes ORDER BY nombre_completo")->fetch_all(MYSQLI_ASSOC);}
$titulo='Visitantes';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1>Administrar visitantes</h1><p>Consultar, crear, editar y eliminar registros que todavía no formen parte del historial.</p></div><div><a class="btn primary" href="visitante_form.php">+ Nuevo visitante</a> <a class="btn ghost" href="dashboard.php">← Volver</a></div></div>
<form class="searchbar"><input name="q" value="<?=e($q)?>" placeholder="Buscar por DNI o nombre"><button class="btn secondary">Buscar</button><?php if($q):?><a class="btn ghost" href="visitantes.php">Limpiar</a><?php endif;?></form>
<div class="table-card"><table><thead><tr><th>DNI</th><th>Nombre</th><th>Teléfono</th><th>Registro</th><th>Acciones</th></tr></thead><tbody><?php foreach($lista as $v):?><tr><td><?=e($v['numero_identidad'])?></td><td><strong><?=e($v['nombre_completo'])?></strong></td><td><?=e($v['telefono']?:'—')?></td><td><?=e($v['creado_en'])?></td><td><div class="row-actions"><a class="btn small secondary" href="visitante_form.php?id=<?=$v['id_visitante']?>">Editar</a><form method="post" onsubmit="return confirm('¿Eliminar este visitante?');"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$v['id_visitante']?>"><button class="btn small danger">Eliminar</button></form></div></td></tr><?php endforeach;?></tbody></table></div>
<?php include __DIR__.'/../footer.php'; ?>
