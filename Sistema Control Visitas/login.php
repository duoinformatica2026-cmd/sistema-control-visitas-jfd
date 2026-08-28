<?php
require_once __DIR__.'/funciones.php';
if(isLoggedIn()) redirect('index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $usuario=trim($_POST['usuario']??'');
    $clave=$_POST['clave']??'';
    $stmt=mysqli_prepare($conn,"SELECT * FROM usuarios WHERE nombre_usuario=? AND estado=1 LIMIT 1");
    mysqli_stmt_bind_param($stmt,'s',$usuario); mysqli_stmt_execute($stmt);
    $u=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if($u && password_verify($clave,$u['password_hash'])){
        session_regenerate_id(true);
        $_SESSION['user_id']=$u['id_usuario'];
        $_SESSION['user_name']=$u['nombre_completo'];
        $_SESSION['user_rol']=$u['rol'];
        redirect('index.php');
    }
    $error='Usuario o contraseña incorrectos.';
}
$titulo='Iniciar sesión'; $base='.'; include __DIR__.'/header.php'; ?>
<section class="auth-wrap"><div class="auth-card"><img class="login-logo" src="assets/logo.png" alt="Logo del instituto"><h1>Control de Visitas</h1><p class="muted">C.E.M.G. Técnico “Dr. Jorge Fidel Durón”</p>
<?php if($error): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?>
<form method="post"><label>Usuario<input name="usuario" required autocomplete="username"></label><label>Contraseña<input type="password" name="clave" required autocomplete="current-password"></label><button class="btn primary full">Ingresar</button></form>
</div></section><?php include __DIR__.'/footer.php'; ?>
