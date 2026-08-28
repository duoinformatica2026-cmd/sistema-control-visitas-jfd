<?php
require_once __DIR__.'/../funciones.php';
requireRole('Admin','..');
$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'generar';
    try{
        if($accion==='generar'){
            $cantidad=max(1,min(100,(int)($_POST['cantidad']??1)));
            mysqli_begin_transaction($conn);
            for($i=0;$i<$cantidad;$i++){
                do{
                    $codigo='JFD-'.strtoupper(substr(bin2hex(random_bytes(8)),0,12));
                    $st=mysqli_prepare($conn,"SELECT 1 FROM codigos_qr WHERE codigo=?");
                    mysqli_stmt_bind_param($st,'s',$codigo);mysqli_stmt_execute($st);$ex=mysqli_stmt_get_result($st)->num_rows>0;
                }while($ex);
                $uid=(int)$_SESSION['user_id'];
                $st=mysqli_prepare($conn,"INSERT INTO codigos_qr(codigo,estado,activo,creado_por) VALUES(?,'Disponible',1,?)");
                mysqli_stmt_bind_param($st,'si',$codigo,$uid);mysqli_stmt_execute($st);
            }
            mysqli_commit($conn);flash('success',"Se generaron $cantidad código(s) QR reutilizables.");redirect('codigos_qr.php');
        }
        if($accion==='guardar'){
            $id=(int)($_POST['id']??0);$codigo=strtoupper(trim($_POST['codigo']??''));
            if($codigo==='')throw new Exception('El código no puede quedar vacío.');
            if($id){
                $st=mysqli_prepare($conn,"SELECT estado FROM codigos_qr WHERE id_codigo_qr=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$r=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
                if(!$r)throw new Exception('Código QR no encontrado.');
                if($r['estado']==='Asignado')throw new Exception('No puede editar un QR mientras está asignado a una visita.');
                $st=mysqli_prepare($conn,"UPDATE codigos_qr SET codigo=? WHERE id_codigo_qr=?");mysqli_stmt_bind_param($st,'si',$codigo,$id);mysqli_stmt_execute($st);
            }else{
                $uid=(int)$_SESSION['user_id'];$st=mysqli_prepare($conn,"INSERT INTO codigos_qr(codigo,estado,activo,creado_por) VALUES(?,'Disponible',1,?)");mysqli_stmt_bind_param($st,'si',$codigo,$uid);mysqli_stmt_execute($st);
            }
            flash('success','Código QR guardado correctamente.');redirect('codigos_qr.php');
        }
        if($accion==='toggle'){
            $id=(int)($_POST['id']??0);
            $st=mysqli_prepare($conn,"SELECT estado,activo FROM codigos_qr WHERE id_codigo_qr=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$r=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
            if(!$r)throw new Exception('Código no encontrado.');
            if($r['estado']==='Asignado')throw new Exception('No puede desactivar un QR mientras está asignado.');
            $nuevo=(int)!$r['activo'];$estado=$nuevo?'Disponible':'Inactivo';
            $st=mysqli_prepare($conn,"UPDATE codigos_qr SET activo=?,estado=? WHERE id_codigo_qr=?");mysqli_stmt_bind_param($st,'isi',$nuevo,$estado,$id);mysqli_stmt_execute($st);
            flash('success',$nuevo?'Código QR activado.':'Código QR desactivado.');redirect('codigos_qr.php');
        }
        if($accion==='eliminar'){
            $id=(int)($_POST['id']??0);
            $st=mysqli_prepare($conn,"SELECT c.estado,(SELECT COUNT(*) FROM visitas v WHERE v.id_codigo_qr=c.id_codigo_qr) usos FROM codigos_qr c WHERE c.id_codigo_qr=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$r=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
            if(!$r)throw new Exception('Código no encontrado.');
            if($r['estado']==='Asignado')throw new Exception('No puede eliminar un QR asignado.');
            if((int)$r['usos']>0)throw new Exception('Este QR ya forma parte del historial. Desactívelo en lugar de eliminarlo.');
            $st=mysqli_prepare($conn,"DELETE FROM codigos_qr WHERE id_codigo_qr=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);
            flash('success','Código QR eliminado.');redirect('codigos_qr.php');
        }
    }catch(Throwable $e){if(mysqli_errno($conn)){} if(isset($accion)&&$accion==='generar')@mysqli_rollback($conn);$error=$e->getMessage();}
}

$editar=null;if(isset($_GET['editar'])){$id=(int)$_GET['editar'];$st=mysqli_prepare($conn,"SELECT * FROM codigos_qr WHERE id_codigo_qr=?");mysqli_stmt_bind_param($st,'i',$id);mysqli_stmt_execute($st);$editar=mysqli_fetch_assoc(mysqli_stmt_get_result($st));}
$r=mysqli_query($conn,"SELECT c.*,u.nombre_completo creador,(SELECT COUNT(*) FROM visitas v WHERE v.id_codigo_qr=c.id_codigo_qr) usos,(SELECT vi.nombre_completo FROM visitas v JOIN visitantes vi ON vi.id_visitante=v.id_visitante WHERE v.id_codigo_qr=c.id_codigo_qr AND v.estado_visita IN ('Pendiente','Activa') ORDER BY v.id_visita DESC LIMIT 1) visitante_actual FROM codigos_qr c LEFT JOIN usuarios u ON u.id_usuario=c.creado_por ORDER BY c.id_codigo_qr");
$lista=$r->fetch_all(MYSQLI_ASSOC);
$titulo='Códigos QR';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head no-print"><div><h1>Códigos QR reutilizables</h1><p>El administrador crea, edita, activa, desactiva e imprime los QR. Al marcar la salida de un visitante, el QR vuelve a estar disponible.</p></div><div><button class="btn secondary" type="button" onclick="window.print()">🖨️ Imprimir QR (7 × 9 cm)</button> <a class="btn ghost" href="dashboard.php">← Volver</a></div></div>
<?php if($error):?><div class="alert danger no-print"><?=e($error)?></div><?php endif;?>
<div class="grid actions no-print" style="margin-bottom:18px">
<form method="post" class="card inline-form" style="margin:0"><input type="hidden" name="accion" value="generar"><label class="grow">Generar automáticamente<input type="number" name="cantidad" min="1" max="100" value="1" required></label><button class="btn primary">Generar</button></form>
<form method="post" class="card inline-form" style="margin:0"><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?=e($editar['id_codigo_qr']??'')?>"><label class="grow"><?=$editar?'Editar':'Crear manualmente'?><input name="codigo" maxlength="80" value="<?=e($editar['codigo']??'')?>" placeholder="JFD-QR-0011" required></label><button class="btn secondary">Guardar</button><?php if($editar):?><a class="btn ghost" href="codigos_qr.php">Cancelar</a><?php endif;?></form>
</div>
<div class="qr-admin-grid">
<?php foreach($lista as $c): ?>
<article class="qr-admin-card <?=!$c['activo']?'qr-disabled':''?>">
<img class="qr-card-logo print-only" src="../assets/logo.png" alt="Logo del instituto"><div class="qr-card-title print-only">CONTROL DE VISITAS</div>
<div class="qr-mini" data-code="<?=e($c['codigo'])?>"></div><code><?=e($c['codigo'])?></code>
<span class="badge no-print <?=$c['estado']==='Disponible'?'success':($c['estado']==='Asignado'?'warning':'secondary')?>"><?=e($c['estado'])?></span>
<?php if($c['visitante_actual']):?><small class="no-print">Asignado a: <?=e($c['visitante_actual'])?></small><?php endif;?><small class="no-print">Usos históricos: <?=(int)$c['usos']?></small>
<div class="row-actions no-print"><a class="btn small secondary" href="?editar=<?=$c['id_codigo_qr']?>">Editar</a><form method="post"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id" value="<?=$c['id_codigo_qr']?>"><button class="btn small ghost" <?=$c['estado']==='Asignado'?'disabled':''?>><?=$c['activo']?'Desactivar':'Activar'?></button></form><form method="post" onsubmit="return confirm('¿Eliminar este QR? Solo se podrá eliminar si nunca ha sido usado.');"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$c['id_codigo_qr']?>"><button class="btn small danger" <?=$c['estado']==='Asignado'?'disabled':''?>>Eliminar</button></form></div>
<small class="qr-card-note print-only">Gafete QR reutilizable • Devuélvalo al salir</small>
</article><?php endforeach; ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script><script>document.querySelectorAll('.qr-mini').forEach(el=>{if(window.QRCode){new QRCode(el,{text:el.dataset.code,width:220,height:220,correctLevel:QRCode.CorrectLevel.H});}else{el.textContent=el.dataset.code;}});</script>
<?php include __DIR__.'/../footer.php'; ?>
