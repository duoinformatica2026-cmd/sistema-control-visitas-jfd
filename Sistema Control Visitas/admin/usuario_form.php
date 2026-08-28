<?php
require_once __DIR__.'/../funciones.php'; requireRole('Admin','..');
$id=(int)($_GET['id']??0);$u=['nombre_usuario'=>'','nombre_completo'=>''];
if($id){$st=mysqli_prepare($conn,"SELECT * FROM usuarios WHERE id_usuario=? AND rol='Guardia'");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$u=mysqli_fetch_assoc(mysqli_stmt_get_result($st));if(!$u)redirect('usuarios.php');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $usr=trim($_POST['usuario']??'');$nom=trim($_POST['nombre']??'');$clave=$_POST['clave']??'';
 if(!$usr||!$nom||(!$id&&!$clave))$error='Complete los campos obligatorios.';
 else{try{
   if($id){if($clave!==''){$hash=password_hash($clave,PASSWORD_DEFAULT);$st=mysqli_prepare($conn,"UPDATE usuarios SET nombre_usuario=?,nombre_completo=?,password_hash=? WHERE id_usuario=? AND rol='Guardia'");mysqli_stmt_bind_param($st,'sssi',$usr,$nom,$hash,$id);}else{$st=mysqli_prepare($conn,"UPDATE usuarios SET nombre_usuario=?,nombre_completo=? WHERE id_usuario=? AND rol='Guardia'");mysqli_stmt_bind_param($st,'ssi',$usr,$nom,$id);}}
   else{$hash=password_hash($clave,PASSWORD_DEFAULT);$rol='Guardia';$st=mysqli_prepare($conn,"INSERT INTO usuarios(nombre_usuario,password_hash,nombre_completo,rol,estado) VALUES(?,?,?,?,1)");mysqli_stmt_bind_param($st,'ssss',$usr,$hash,$nom,$rol);}
   mysqli_stmt_execute($st);flash('success','Guardia guardado correctamente.');redirect('usuarios.php');
 }catch(Throwable $e){$error='No se pudo guardar. Verifique que el nombre de usuario no esté repetido.';}}
}
$titulo=$id?'Editar guardia':'Nuevo guardia';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1><?=$id?'Editar guardia':'Nuevo guardia'?></h1><p>El administrador puede cambiar tanto el usuario como la contraseña.</p></div><a class="btn ghost" href="usuarios.php">← Volver</a></div>
<?php if($error):?><div class="alert danger"><?=e($error)?></div><?php endif;?>
<form method="post" class="card form-grid"><label>Usuario *<input name="usuario" required value="<?=e($u['nombre_usuario']??'')?>"></label><label>Nombre completo *<input name="nombre" required value="<?=e($u['nombre_completo']??'')?>"></label><label class="span-2">Contraseña <?=$id?'(déjela vacía para conservar la actual)':'*'?><input type="password" name="clave" <?=$id?'':'required'?>></label><button class="btn primary span-2">Guardar guardia</button></form>
<?php include __DIR__.'/../footer.php'; ?>
