<?php
require_once __DIR__.'/../funciones.php';
header('Content-Type: application/json; charset=utf-8');
if(!isLoggedIn()||!hasRole('Guardia')){http_response_code(401);echo json_encode(['ok'=>false,'mensaje'=>'Sesión de guardia requerida.']);exit;}
$body=json_decode(file_get_contents('php://input'),true)?:$_POST;
$codigo=trim($body['codigo']??'');
if(!$codigo){echo json_encode(['ok'=>false,'mensaje'=>'Código vacío.']);exit;}
echo json_encode(procesarCodigoEntrada($conn,$codigo),JSON_UNESCAPED_UNICODE);
