<?php
require_once __DIR__.'/../funciones.php'; requireRole('Admin','..');
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';$id=(int)($_POST['id']??0);
    if($accion==='toggle'){
        $st=mysqli_prepare($conn,"UPDATE usuarios SET estado=IF(estado=1,0,1) WHERE id_usuario=? AND rol='Guardia'");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);flash('success','Estado del guardia actualizado.');redirect('usuarios.php');
    }
    if($accion==='eliminar'){
        $st=mysqli_prepare($conn,"SELECT COUNT(*) total FROM visitas WHERE id_usuario_registro=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$usos=(int)(mysqli_fetch_assoc(mysqli_stmt_get_result($st))['total']??0);
        if($usos>0){flash('danger','No se puede eliminar este guardia porque tiene visitas registradas. Puede desactivarlo para conservar el historial.');redirect('usuarios.php');}
        $st=mysqli_prepare($conn,"DELETE FROM usuarios WHERE id_usuario=? AND rol='Guardia'");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);flash('success','Guardia eliminado.');redirect('usuarios.php');
    }
}
$r=mysqli_query($conn,"SELECT id_usuario,nombre_usuario,nombre_completo,estado,creado_en FROM usuarios WHERE rol='Guardia' ORDER BY nombre_completo");$lista=$r->fetch_all(MYSQLI_ASSOC);
$titulo='Guardias';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1>Administrar guardias</h1><p>CRUD de cuentas de guardia: crear, consultar, editar, cambiar contraseña, activar/desactivar y eliminar cuando no tengan historial.</p></div><div><a class="btn primary" href="usuario_form.php">+ Nuevo guardia</a> <a class="btn ghost" href="dashboard.php">← Volver</a></div></div>
<div class="table-card"><table><thead><tr><th>Usuario</th><th>Nombre</th><th>Estado</th><th>Creado</th><th>Acciones</th></tr></thead><tbody><?php foreach($lista as $u):?><tr><td><strong><?=e($u['nombre_usuario'])?></strong></td><td><?=e($u['nombre_completo'])?></td><td><span class="badge <?=$u['estado']?'success':'danger'?>"><?=$u['estado']?'Activo':'Inactivo'?></span></td><td><?=e($u['creado_en'])?></td><td><div class="row-actions"><a class="btn small secondary" href="usuario_form.php?id=<?=$u['id_usuario']?>">Editar</a><form method="post"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id" value="<?=$u['id_usuario']?>"><button class="btn small ghost"><?=$u['estado']?'Desactivar':'Activar'?></button></form><form method="post" onsubmit="return confirm('¿Eliminar este guardia?');"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$u['id_usuario']?>"><button class="btn small danger">Eliminar</button></form></div></td></tr><?php endforeach;?></tbody></table></div>
<?php include __DIR__.'/../footer.php'; ?>
