<?php
require_once __DIR__ . '/db.php';

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function flash(string $tipo,string $mensaje): void { $_SESSION['flash']=['tipo'=>$tipo,'mensaje'=>$mensaje]; }
function takeFlash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function redirect(string $url): never { header('Location: '.$url); exit; }
function estadoClase(string $estado): string {
    return match($estado){
        'Activa'=>'success','Finalizada'=>'secondary','Pendiente'=>'warning','Cancelada'=>'danger',default=>'secondary'
    };
}
function contar(mysqli $conn,string $sql): int {
    $r=mysqli_query($conn,$sql); $row=$r?mysqli_fetch_row($r):[0]; return (int)($row[0]??0);
}
function getCodigosDisponibles(mysqli $conn): array {
    $r=mysqli_query($conn,"SELECT id_codigo_qr,codigo FROM codigos_qr WHERE estado='Disponible' AND activo=1 ORDER BY id_codigo_qr");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

/* El escáner SOLO registra entradas. Nunca registra salidas. */
function procesarCodigoEntrada(mysqli $conn,string $codigo): array {
    mysqli_begin_transaction($conn);
    try {
        $st=mysqli_prepare($conn,"SELECT c.id_codigo_qr,c.codigo,c.estado estado_qr,c.activo,
                    v.id_visita,v.estado_visita,v.id_visitante,
                    vi.nombre_completo,vi.numero_identidad
                FROM codigos_qr c
                LEFT JOIN visitas v ON v.id_codigo_qr=c.id_codigo_qr AND v.estado_visita IN ('Pendiente','Activa')
                LEFT JOIN visitantes vi ON vi.id_visitante=v.id_visitante
                WHERE c.codigo=?
                ORDER BY v.id_visita DESC LIMIT 1 FOR UPDATE");
        mysqli_stmt_bind_param($st,'s',$codigo); mysqli_stmt_execute($st);
        $row=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
        if(!$row) throw new Exception('Código QR no autorizado.');
        if(!(int)$row['activo']) throw new Exception('Este código QR está desactivado por el administrador.');
        if(empty($row['id_visita'])) throw new Exception('Este QR no está asignado a ningún visitante.');
        if($row['estado_visita']==='Activa') throw new Exception('La entrada de este visitante ya fue registrada. La salida se marca desde “Personas en el instituto”.');
        if($row['estado_visita']!=='Pendiente') throw new Exception('Este QR no tiene una visita pendiente válida.');

        $up=mysqli_prepare($conn,"UPDATE visitas SET estado_visita='Activa',hora_entrada=NOW() WHERE id_visita=? AND estado_visita='Pendiente'");
        mysqli_stmt_bind_param($up,'i',$row['id_visita']); mysqli_stmt_execute($up);
        if(mysqli_stmt_affected_rows($up)!==1) throw new Exception('No se pudo registrar la entrada. Intente nuevamente.');

        mysqli_commit($conn);
        return ['ok'=>true,'accion'=>'entrada','mensaje'=>'Entrada registrada correctamente.','visitante'=>$row['nombre_completo'],'dni'=>$row['numero_identidad']];
    } catch(Throwable $e) {
        mysqli_rollback($conn);
        return ['ok'=>false,'mensaje'=>$e->getMessage()];
    }
}

/* La salida se registra manualmente y libera el QR para reutilizarlo. */
function registrarSalida(mysqli $conn,int $idVisita): array {
    mysqli_begin_transaction($conn);
    try {
        $st=mysqli_prepare($conn,"SELECT v.id_visita,v.id_codigo_qr,v.estado_visita,vi.nombre_completo
                FROM visitas v JOIN visitantes vi ON vi.id_visitante=v.id_visitante
                WHERE v.id_visita=? FOR UPDATE");
        mysqli_stmt_bind_param($st,'i',$idVisita); mysqli_stmt_execute($st);
        $v=mysqli_fetch_assoc(mysqli_stmt_get_result($st));
        if(!$v) throw new Exception('La visita no existe.');
        if($v['estado_visita']!=='Activa') throw new Exception('Solo se puede marcar salida de una persona que está dentro del instituto.');

        $up=mysqli_prepare($conn,"UPDATE visitas SET estado_visita='Finalizada',hora_salida=NOW() WHERE id_visita=?");
        mysqli_stmt_bind_param($up,'i',$idVisita); mysqli_stmt_execute($up);

        $up=mysqli_prepare($conn,"UPDATE codigos_qr SET estado='Disponible',asignado_en=NULL WHERE id_codigo_qr=? AND activo=1");
        mysqli_stmt_bind_param($up,'i',$v['id_codigo_qr']); mysqli_stmt_execute($up);

        mysqli_commit($conn);
        return ['ok'=>true,'mensaje'=>'Salida registrada. El código QR quedó disponible para reutilizarse.','visitante'=>$v['nombre_completo']];
    } catch(Throwable $e) {
        mysqli_rollback($conn);
        return ['ok'=>false,'mensaje'=>$e->getMessage()];
    }
}

function liberarQrDeVisita(mysqli $conn,int $idQr): void {
    $st=mysqli_prepare($conn,"UPDATE codigos_qr SET estado=IF(activo=1,'Disponible','Inactivo'),asignado_en=NULL WHERE id_codigo_qr=?");
    mysqli_stmt_bind_param($st,'i',$idQr); mysqli_stmt_execute($st);
}
