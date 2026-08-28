<?php
require_once __DIR__.'/../funciones.php'; requireRole('Guardia','..');
$error=''; $encontrado=null;
if(isset($_GET['dni']) && trim($_GET['dni'])!==''){
    $dni=trim($_GET['dni']);
    $st=mysqli_prepare($conn,"SELECT * FROM visitantes WHERE numero_identidad=? LIMIT 1");
    mysqli_stmt_bind_param($st,'s',$dni);mysqli_stmt_execute($st);
    $encontrado=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $dni=trim($_POST['dni']??''); $nombre=trim($_POST['nombre']??''); $telefono=trim($_POST['telefono']??'');
    $persona=trim($_POST['persona']??''); $area=trim($_POST['area']??''); $motivo=trim($_POST['motivo']??'');
    $idQr=(int)($_POST['id_codigo_qr']??0);
    if(!$dni||!$nombre||!$persona||!$area||!$motivo||!$idQr){
        $error='Complete todos los campos obligatorios y seleccione un código QR.';
    }else{
        mysqli_begin_transaction($conn);
        try{
            $st=mysqli_prepare($conn,"SELECT id_visitante FROM visitantes WHERE numero_identidad=? FOR UPDATE");
            mysqli_stmt_bind_param($st,'s',$dni);mysqli_stmt_execute($st);$r=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
            if($r){
                $idv=(int)$r['id_visitante'];
                $up=mysqli_prepare($conn,"UPDATE visitantes SET nombre_completo=?,telefono=? WHERE id_visitante=?");
                mysqli_stmt_bind_param($up,'ssi',$nombre,$telefono,$idv);mysqli_stmt_execute($up);
            }else{
                $in=mysqli_prepare($conn,"INSERT INTO visitantes(numero_identidad,nombre_completo,telefono) VALUES(?,?,?)");
                mysqli_stmt_bind_param($in,'sss',$dni,$nombre,$telefono);mysqli_stmt_execute($in);$idv=mysqli_insert_id($conn);
            }
            $st=mysqli_prepare($conn,"SELECT codigo FROM codigos_qr WHERE id_codigo_qr=? AND estado='Disponible' AND activo=1 FOR UPDATE");
            mysqli_stmt_bind_param($st,'i',$idQr);mysqli_stmt_execute($st);$qr=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
            if(!$qr)throw new Exception('El código QR seleccionado ya no está disponible. Seleccione otro.');
            $codigo=$qr['codigo']; $uid=(int)$_SESSION['user_id'];
            $ins=mysqli_prepare($conn,"INSERT INTO visitas(id_visitante,id_usuario_registro,id_codigo_qr,codigo_qr,motivo,persona_a_visitar,area_destino,fecha_registro,estado_visita) VALUES(?,?,?,?,?,?,?,NOW(),'Pendiente')");
            mysqli_stmt_bind_param($ins,'iiissss',$idv,$uid,$idQr,$codigo,$motivo,$persona,$area);mysqli_stmt_execute($ins);$id=mysqli_insert_id($conn);
            $up=mysqli_prepare($conn,"UPDATE codigos_qr SET estado='Asignado',asignado_en=NOW() WHERE id_codigo_qr=?");
            mysqli_stmt_bind_param($up,'i',$idQr);mysqli_stmt_execute($up);
            mysqli_commit($conn); redirect('pase.php?id='.$id);
        }catch(Throwable $e){mysqli_rollback($conn);$error=$e->getMessage();}
    }
}
$codigos=getCodigosDisponibles($conn); $titulo='Registrar visitante';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1>Registro de visitante</h1><p>Escriba el DNI. Si la persona ya visitó el instituto, solo se recuperan sus datos personales; motivo, destino y persona a visitar quedan vacíos para la nueva visita.</p></div><a class="btn ghost" href="dashboard.php">← Volver</a></div>
<?php if($error):?><div class="alert danger"><?=e($error)?></div><?php endif;?>
<?php if(!$codigos):?><div class="alert warning">No hay códigos QR disponibles. Solicite al administrador que habilite o cree más códigos.</div><?php endif;?>
<form method="post" class="card form-grid" id="formVisita">
<h2 class="span-2">Datos personales</h2>
<label>DNI *<input id="dni" name="dni" required value="<?=e($encontrado['numero_identidad']??($_GET['dni']??''))?>" autocomplete="off"></label>
<label>Nombre completo *<input id="nombre" name="nombre" required value="<?=e($encontrado['nombre_completo']??'')?>"></label>
<label>Teléfono<input id="telefono" name="telefono" value="<?=e($encontrado['telefono']??'')?>"></label>
<label>Código QR / gafete *<select name="id_codigo_qr" required><option value="">Seleccione un QR disponible...</option><?php foreach($codigos as $q):?><option value="<?=$q['id_codigo_qr']?>"><?=e($q['codigo'])?></option><?php endforeach;?></select></label>
<h2 class="span-2">Datos de esta visita</h2>
<label>Persona a visitar *<input id="persona" name="persona" required value=""></label>
<label>Área de destino *<input id="area" name="area" required value=""></label>
<label class="span-2">Motivo *<textarea id="motivo" name="motivo" required></textarea></label>
<div id="dniInfo" class="span-2 muted small"></div>
<button class="btn primary span-2" <?=$codigos?'':'disabled'?>>Guardar y asignar QR</button>
</form>
<script>
let timer;const dni=document.getElementById('dni'),nombre=document.getElementById('nombre'),telefono=document.getElementById('telefono'),persona=document.getElementById('persona'),area=document.getElementById('area'),motivo=document.getElementById('motivo'),dniInfo=document.getElementById('dniInfo');
dni.addEventListener('input',e=>{clearTimeout(timer);const valor=e.target.value.trim();if(valor.length<5){dniInfo.textContent='';return;}timer=setTimeout(async()=>{try{const r=await fetch('../api/visitante_por_dni.php?dni='+encodeURIComponent(valor));const d=await r.json();if(d.ok&&d.encontrado){nombre.value=d.visitante.nombre_completo||'';telefono.value=d.visitante.telefono||'';persona.value='';area.value='';motivo.value='';dniInfo.textContent='Visitante encontrado: se recuperaron únicamente sus datos personales. Complete los datos de esta nueva visita.';}else{nombre.value='';telefono.value='';persona.value='';area.value='';motivo.value='';dniInfo.textContent='DNI nuevo. Complete los datos del visitante.';}}catch(_){dniInfo.textContent='No se pudo consultar el DNI.';}},350);});
</script>
<?php include __DIR__.'/../footer.php'; ?>
