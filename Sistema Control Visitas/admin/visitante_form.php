<?php
require_once __DIR__.'/../funciones.php'; requireRole('Admin','..');
$id=(int)($_GET['id']??0);$v=['numero_identidad'=>'','nombre_completo'=>'','telefono'=>''];
if($id){$st=mysqli_prepare($conn,"SELECT * FROM visitantes WHERE id_visitante=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$v=mysqli_fetch_assoc(mysqli_stmt_get_result($st));if(!$v)redirect('visitantes.php');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $dni=trim($_POST['dni']??'');$nombre=trim($_POST['nombre']??'');$telefono=trim($_POST['telefono']??'');
    if(!$dni||!$nombre)$error='DNI y nombre son obligatorios.';
    else{try{if($id){$st=mysqli_prepare($conn,"UPDATE visitantes SET numero_identidad=?,nombre_completo=?,telefono=? WHERE id_visitante=?");mysqli_stmt_bind_param($st,'sssi',$dni,$nombre,$telefono,$id);}else{$st=mysqli_prepare($conn,"INSERT INTO visitantes(numero_identidad,nombre_completo,telefono) VALUES(?,?,?)");mysqli_stmt_bind_param($st,'sss',$dni,$nombre,$telefono);}mysqli_stmt_execute($st);flash('success','Visitante guardado correctamente.');redirect('visitantes.php');}catch(Throwable $e){$error='No se pudo guardar. Verifique que el DNI no esté repetido.';}}
}
$titulo=$id?'Editar visitante':'Nuevo visitante';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1><?=$id?'Editar visitante':'Nuevo visitante'?></h1><p>Datos personales reutilizados en futuras visitas.</p></div><a class="btn ghost" href="visitantes.php">← Volver</a></div>
<?php if($error):?><div class="alert danger"><?=e($error)?></div><?php endif;?>
<form method="post" class="card form-grid"><label>DNI *<input name="dni" required value="<?=e($v['numero_identidad'])?>"></label><label>Nombre completo *<input name="nombre" required value="<?=e($v['nombre_completo'])?>"></label><label class="span-2">Teléfono<input name="telefono" value="<?=e($v['telefono'])?>"></label><button class="btn primary span-2">Guardar visitante</button></form>
<?php include __DIR__.'/../footer.php'; ?>
