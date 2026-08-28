<?php require_once __DIR__.'/../funciones.php';requireRole('Guardia','..');$titulo='Escáner QR';$base='..';include __DIR__.'/../header.php'; ?>
<div class="page-head"><div><h1>Escáner QR de entrada</h1><p>El escáner solo registra ingresos. La salida se marca desde “Personas en el instituto”. Solo acepta QR creados y habilitados por el administrador.</p></div><a class="btn ghost" href="dashboard.php">← Volver</a></div>
<div class="scanner-layout"><div class="card"><div id="reader" class="reader"></div><div class="manual-scan"><input id="codigo_manual" placeholder="JFD-QR-0001"><button class="btn secondary" type="button" id="btnManual">Procesar entrada</button></div><p class="muted small">En Railway la cámara funciona mediante HTTPS. Si la cámara no está disponible, escriba el código manualmente.</p></div><div class="card result-card" id="resultado"><div><h3>Esperando código…</h3><p class="muted">Aquí aparecerá el resultado de la entrada.</p></div></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script><script>
let bloqueado=false;
async function procesar(codigo){
 codigo=(codigo||'').trim(); if(!codigo||bloqueado)return; bloqueado=true;
 const box=document.getElementById('resultado'); box.innerHTML='<h3>Procesando entrada…</h3>';
 try{
   const r=await fetch('../api/procesar_qr.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({codigo})});
   const d=await r.json();
   box.innerHTML=d.ok?`<div class="scan-ok"><div class="big-icon">🟢</div><h2>${d.mensaje}</h2><p><strong>${d.visitante}</strong></p><p>DNI: ${d.dni}</p><button class="btn primary" type="button" id="otro">Escanear otro</button></div>`:`<div class="scan-error"><div class="big-icon">❌</div><h2>${d.mensaje}</h2><button class="btn secondary" type="button" id="reintentar">Intentar de nuevo</button></div>`;
   const b=box.querySelector('button'); if(b)b.addEventListener('click',()=>{bloqueado=false;box.innerHTML='<div><h3>Esperando código…</h3><p class="muted">Aquí aparecerá el resultado de la entrada.</p></div>';});
 }catch(e){box.innerHTML='<div class="scan-error"><h2>Error de conexión</h2><p>Revise la conexión del sistema.</p><button class="btn secondary" type="button">Intentar de nuevo</button></div>';const b=box.querySelector('button');if(b)b.addEventListener('click',()=>bloqueado=false);}
 setTimeout(()=>{bloqueado=false;},1600);
}
document.getElementById('btnManual').addEventListener('click',()=>procesar(document.getElementById('codigo_manual').value));
document.getElementById('codigo_manual').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();procesar(e.target.value);}});
if(window.Html5QrcodeScanner){const scanner=new Html5QrcodeScanner('reader',{fps:10,qrbox:{width:250,height:250},rememberLastUsedCamera:true,supportedScanTypes:[Html5QrcodeScanType.SCAN_TYPE_CAMERA]},false);scanner.render(t=>procesar(t),()=>{});}else{document.getElementById('reader').innerHTML='<div class="alert warning">No se pudo cargar el lector de cámara. Use el campo manual.</div>';}
</script><?php include __DIR__.'/../footer.php'; ?>
