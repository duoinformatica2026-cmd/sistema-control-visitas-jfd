<?php
require_once __DIR__.'/../funciones.php';
header('Content-Type: application/json; charset=utf-8');
if(!isLoggedIn()||!hasRole('Guardia')){http_response_code(401);echo json_encode(['ok'=>false]);exit;}
$dni=trim($_GET['dni']??'');
if($dni===''){echo json_encode(['ok'=>true,'encontrado'=>false]);exit;}
$st=mysqli_prepare($conn,"SELECT id_visitante,numero_identidad,nombre_completo,telefono FROM visitantes WHERE numero_identidad=? LIMIT 1");
mysqli_stmt_bind_param($st,'s',$dni);mysqli_stmt_execute($st);
$v=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if(!$v){echo json_encode(['ok'=>true,'encontrado'=>false]);exit;}
/* Solo se devuelven datos personales. Motivo y destino siempre quedan en blanco. */
echo json_encode(['ok'=>true,'encontrado'=>true,'visitante'=>$v],JSON_UNESCAPED_UNICODE);
