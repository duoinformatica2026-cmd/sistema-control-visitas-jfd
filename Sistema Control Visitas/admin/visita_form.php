<?php
require_once __DIR__.'/../funciones.php'; requireRole('Admin','..');
$id=(int)($_GET['id']??0);if(!$id)redirect('visitas.php');
$st=mysqli_prepare($conn,"SELECT v.*,vi.nombre_completo,vi.numero_identidad FROM visitas v JOIN visitantes vi ON vi.id_visitante=v.id_visitante WHERE v.id_visita=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$v=mysqli_fetch_assoc(mysqli_stmt_get_result($st));if(!$v)redirect('visitas.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){$persona=trim($_POST['persona']??'');$area=trim($_POST['area']??'');$motivo=trim($_POST['motivo']??'');if(!$persona||!$area||!$motivo)$error='Complete todos los campos.';else{$st=mysqli_prepare($conn,"UPDATE visitas SET persona_a_visitar=?,area_destino=?,motivo=? WHERE id_visita=?");mysqli_stmt_bind_param($st,'sssi',$persona,$area,$motivo,$id);mysqli_stmt_execute($st);flash('success','Visita actualizada.');redirect('visitas.php');}}
$titulo='Editar visita';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1>Editar visita</h1><p><?=e($v['nombre_completo'])?> · <?=e($v['numero_identidad'])?></p></div><a class="btn ghost" href="visitas.php">← Volver</a></div>
<?php if($error):?><div class="alert danger"><?=e($error)?></div><?php endif;?>
<form method="post" class="card form-grid"><label>Persona a visitar *<input name="persona" required value="<?=e($v['persona_a_visitar'])?>"></label><label>Área de destino *<input name="area" required value="<?=e($v['area_destino'])?>"></label><label class="span-2">Motivo *<textarea name="motivo" required><?=e($v['motivo'])?></textarea></label><button class="btn primary span-2">Guardar cambios</button></form>
<?php include __DIR__.'/../footer.php'; ?>
