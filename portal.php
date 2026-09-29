<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$medicoSesion = $_SESSION['medico_autenticado'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal de Acceso Médico • UPSS Patología Clínica | Hospital Pacasmayo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/laboratorio.css">
  <style>
    :root {
      --portal-bg: #0f172a;
      --portal-card-bg: rgba(255, 255, 255, 0.95);
      --portal-accent: #0284c7;
      --portal-accent-hover: #0369a1;
    }

    body.portal-body {
      min-height: 100vh;
      background: radial-gradient(circle at 10% 20%, rgba(2, 132, 199, 0.18) 0%, transparent 40%),
                  radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 40%),
                  linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      color: #1e293b;
      display: flex;
      flex-direction: column;
      margin: 0;
      padding: 0;
    }

    .portal-header {
      background: rgba(15, 23, 42, 0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding: 14px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .portal-brand {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .portal-brand img {
      height: 42px;
      background: #ffffff;
      padding: 4px 8px;
      border-radius: 6px;
    }

    .portal-brand-text h1 {
      font-size: 16px;
      font-weight: 700;
      color: #ffffff;
      margin: 0;
      letter-spacing: -0.3px;
    }

    .portal-brand-text p {
      font-size: 12px;
      color: #94a3b8;
      margin: 2px 0 0 0;
    }

    .portal-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .portal-card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1);
      width: 100%;
      max-width: 900px;
      overflow: hidden;
      display: grid;
      grid-template-columns: 380px 1fr;
    }

    @media (max-width: 820px) {
      .portal-card {
        grid-template-columns: 1fr;
      }
    }

    .portal-auth-side {
      background: #f8fafc;
      padding: 36px 30px;
      border-right: 1px solid #e2e8f0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .portal-auth-title {
      font-size: 20px;
      font-weight: 800;
      color: #0f172a;
      margin: 0 0 6px 0;
    }

    .portal-auth-subtitle {
      font-size: 13px;
      color: #64748b;
      margin: 0 0 24px 0;
      line-height: 1.45;
    }

    .portal-input-group {
      margin-bottom: 20px;
    }

    .portal-label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: #334155;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .portal-input-wrapper {
      position: relative;
    }

    .portal-input-wrapper svg {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
    }

    .portal-input {
      width: 100%;
      padding: 12px 14px 12px 38px;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
      transition: all 0.2s ease;
      font-family: inherit;
    }

    .portal-input:focus {
      outline: none;
      border-color: var(--portal-accent);
      box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }

    .btn-portal-submit {
      width: 100%;
      background: linear-gradient(135deg, #0284c7, #0369a1);
      color: #ffffff;
      border: none;
      padding: 13px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
    }

    .btn-portal-submit:hover {
      background: linear-gradient(135deg, #0369a1, #075985);
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
    }

    .quick-doctors-section {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px dashed #cbd5e1;
    }

    .quick-doctors-title {
      font-size: 11px;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
    }

    .doctor-pill-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 180px;
      overflow-y: auto;
    }

    .doctor-pill {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 8px 10px;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: left;
    }

    .doctor-pill:hover {
      border-color: var(--portal-accent);
      background: #f0f9ff;
    }

    .doctor-pill-name {
      font-weight: 600;
      color: #1e293b;
    }

    .doctor-pill-spec {
      font-size: 10.5px;
      color: #0284c7;
      font-weight: 500;
    }

    .portal-info-side {
      padding: 36px 32px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .profile-empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      height: 100%;
      min-height: 320px;
      color: #94a3b8;
    }

    .profile-empty-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 16px;
      color: #64748b;
    }

    .profile-active-card {
      display: none;
    }

    .doctor-badge-header {
      display: flex;
      align-items: center;
      gap: 16px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e2e8f0;
      margin-bottom: 20px;
    }

    .doctor-avatar-circle {
      width: 58px;
      height: 58px;
      border-radius: 50%;
      background: linear-gradient(135deg, #0284c7, #38bdf8);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: 800;
      box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
    }

    .doctor-badge-info h3 {
      font-size: 17px;
      font-weight: 800;
      color: #0f172a;
      margin: 0 0 4px 0;
    }

    .doctor-badge-spec {
      display: inline-block;
      background: #e0f2fe;
      color: #0369a1;
      font-size: 11.5px;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 4px;
      margin-bottom: 4px;
    }

    .doctor-badge-cargo {
      font-size: 12px;
      color: #64748b;
    }

    .modules-grid-title {
      font-size: 13px;
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .modules-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 24px;
    }

    @media (max-width: 540px) {
      .modules-grid {
        grid-template-columns: 1fr;
      }
    }

    .module-card {
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      padding: 14px;
      background: #ffffff;
      transition: all 0.2s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
    }

    .module-card.authorized {
      border-color: #bae6fd;
      background: #f0f9ff;
    }

    .module-card.authorized:hover {
      border-color: #0284c7;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15);
    }

    .module-card.restricted {
      opacity: 0.6;
      background: #f8fafc;
      border-color: #e2e8f0;
    }

    .module-card-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }

    .module-icon {
      font-size: 22px;
    }

    .module-name {
      font-size: 13.5px;
      font-weight: 700;
      color: #0f172a;
    }

    .module-sub {
      font-size: 11px;
      color: #64748b;
      margin-bottom: 12px;
      line-height: 1.35;
    }

    .btn-enter-module {
      width: 100%;
      background: #0284c7;
      color: #ffffff;
      border: none;
      padding: 8px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: background 0.2s;
    }

    .btn-enter-module:hover {
      background: #0369a1;
    }

    .btn-enter-module.disabled {
      background: #cbd5e1;
      cursor: not-allowed;
      color: #64748b;
    }

    .portal-footer-note {
      text-align: center;
      font-size: 11.5px;
      color: #64748b;
      padding: 16px;
    }
  </style>
</head>
<body class="portal-body">

  <!-- Encabezado Institucional -->
  <header class="portal-header">
    <div class="portal-brand">
      <img src="assets/img/logo_ris.svg" alt="RIS Pacasmayo">
      <img src="assets/img/logo_hospital.svg" alt="Hospital Pacasmayo">
      <div class="portal-brand-text">
        <h1>HOSPITAL DISTRITAL PACASMAYO</h1>
        <p>UPSS Patología Clínica • Portal de Acceso Médico</p>
      </div>
    </div>
    <div>
      <a href="index.php" class="btn btn-outline" style="color:#ffffff; border-color:rgba(255,255,255,0.25); font-size:12px; padding:6px 14px;">
        Acceso Directo al Sistema &rarr;
      </a>
    </div>
  </header>

  <!-- Contenedor Principal del Portal -->
  <main class="portal-container">
    <div class="portal-card">

      <!-- Lado Izquierdo: Formulario de Autenticación por Correo -->
      <section class="portal-auth-side">
        <div>
          <h2 class="portal-auth-title">Ingreso Médico</h2>
          <p class="portal-auth-subtitle">
            Ingrese su correo institucional registrado para habilitar los exámenes clínicos correspondientes a su especialidad.
          </p>

          <form id="formPortalLogin" onsubmit="event.preventDefault(); identificarMedico();">
            <div class="portal-input-group">
              <label for="txtCorreoMedico" class="portal-label">Correo Institucional / DNI:</label>
              <div class="portal-input-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <input type="text" id="txtCorreoMedico" class="portal-input" placeholder="ej: jsaldana@hospitalpacasmayo.gob.pe" autocomplete="email" required autofocus>
              </div>
            </div>

            <button type="submit" id="btnIdentificar" class="btn-portal-submit">
              <span>Continuar y Validar Especialidad</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
          </form>

          <div id="loginErrorMsg" style="display:none; margin-top:12px; padding:10px 12px; background:#fef2f2; border:1px solid #fecaca; color:#dc2626; border-radius:6px; font-size:12px;"></div>
        </div>

        <!-- Acceso Rapido con Medicos Registrados -->
        <div class="quick-doctors-section">
          <div class="quick-doctors-title">Médicos en Base de Datos (Clic para probar):</div>
          <div class="doctor-pill-list" id="doctorPillList">
            <div style="text-align:center; padding:10px; color:#94a3b8; font-size:11px;">Cargando personal médico...</div>
          </div>
        </div>
      </section>

      <!-- Lado Derecho: Ficha del Médico y Exámenes Permitidos -->
      <section class="portal-info-side">
        <!-- Estado Inicial (Sin Seleccionar) -->
        <div id="profileEmptyState" class="profile-empty-state">
          <div class="profile-empty-icon">🩺</div>
          <h3 style="font-size:16px; font-weight:700; color:#475569; margin:0 0 6px 0;">Identifique su Cuenta Médica</h3>
          <p style="font-size:13px; color:#94a3b8; max-width:320px; margin:0;">
            Al ingresar con su correo, se cargarán automáticamente sus credenciales y los paneles de exámenes clínicos autorizados para su servicio.
          </p>
        </div>

        <!-- Estado Activo (Médico Identificado) -->
        <div id="profileActiveCard" class="profile-active-card">
          <div class="doctor-badge-header">
            <div class="doctor-avatar-circle" id="docAvatar">DR</div>
            <div class="doctor-badge-info">
              <h3 id="docNombreCompleto">Dr. Nombre Completo</h3>
              <div><span class="doctor-badge-spec" id="docEspecialidad">Especialidad</span></div>
              <div class="doctor-badge-cargo" id="docCargoEmail">Cargo • Correo</div>
            </div>
          </div>

          <div>
            <div class="modules-grid-title">
              <span>Paneles de Exámenes Disponibles</span>
              <span style="font-size:11px; color:#0284c7; font-weight:600;">Según Especialidad</span>
            </div>

            <div class="modules-grid" id="containerModulosAutorizados">
              <!-- Renderizado Dinámico de Módulos -->
            </div>
          </div>
        </div>

        <div class="portal-footer-note">
          Gobierno Regional de La Libertad • Red Integrada de Salud Pacasmayo &bull; 2026
        </div>
      </section>

    </div>
  </main>

  <script>
    let medicosCatalogo = [];

    document.addEventListener('DOMContentLoaded', async () => {
      await loadDoctorsCatalog();
      checkActiveSession();
    });

    async function loadDoctorsCatalog() {
      try {
        const res = await fetch('api/auth_medico.php?action=list');
        const data = await res.json();
        if (data.success && data.data) {
          medicosCatalogo = data.data;
          renderDoctorPills(data.data);
        }
      } catch (e) {
        console.error('Error cargando lista de medicos:', e);
      }
    }

    function renderDoctorPills(medicos) {
      const container = document.getElementById('doctorPillList');
      if (!container) return;
      container.innerHTML = '';

      medicos.forEach(m => {
        const div = document.createElement('div');
        div.className = 'doctor-pill';
        div.onclick = () => {
          document.getElementById('txtCorreoMedico').value = m.Correo || m.NumeroDocumento;
          identificarMedico(m.Correo || m.NumeroDocumento);
        };
        div.innerHTML = `
          <div>
            <div class="doctor-pill-name">${escapeHtml(m.NombreCompleto)}</div>
            <div class="doctor-pill-spec">${escapeHtml(m.EspecialidadOcupacion)}</div>
          </div>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        `;
        container.appendChild(div);
      });
    }

    async function checkActiveSession() {
      try {
        const res = await fetch('api/auth_medico.php?action=check');
        const data = await res.json();
        if (data.success && data.authenticated && data.medico) {
          displayDoctorProfile(data.medico);
        }
      } catch (e) {
        console.error('Error verificando sesion:', e);
      }
    }

    async function identificarMedico(correoInput = null) {
      const correo = correoInput || document.getElementById('txtCorreoMedico').value.trim();
      const errBox = document.getElementById('loginErrorMsg');
      const btn = document.getElementById('btnIdentificar');

      if (!correo) {
        if (errBox) {
          errBox.textContent = 'Por favor ingrese un correo institucional válido.';
          errBox.style.display = 'block';
        }
        return;
      }

      if (errBox) errBox.style.display = 'none';
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Verificando especialidad...';
      }

      try {
        const res = await fetch('api/auth_medico.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ correo: correo })
        });
        const data = await res.json();

        if (data.success && data.data) {
          localStorage.setItem('medico_activo', JSON.stringify(data.data));
          displayDoctorProfile(data.data);
        } else {
          if (errBox) {
            errBox.textContent = data.message || 'No se encontró el médico con ese correo.';
            errBox.style.display = 'block';
          }
        }
      } catch (e) {
        console.error('Error identificando medico:', e);
        if (errBox) {
          errBox.textContent = 'Error de conexión con el servidor.';
          errBox.style.display = 'block';
        }
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = `<span>Continuar y Validar Especialidad</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>`;
        }
      }
    }

    function displayDoctorProfile(medico) {
      document.getElementById('profileEmptyState').style.display = 'none';
      const activeCard = document.getElementById('profileActiveCard');
      activeCard.style.display = 'block';

      // Iniciales para avatar
      const nombres = (medico.Nombres || 'DR').split(' ');
      const iniciales = (nombres[0][0] || '') + (medico.ApellidoPaterno ? medico.ApellidoPaterno[0] : '');
      document.getElementById('docAvatar').textContent = iniciales.toUpperCase() || 'DR';

      document.getElementById('docNombreCompleto').textContent = `Dr(a). ${medico.NombreCompleto}`;
      document.getElementById('docEspecialidad').textContent = medico.EspecialidadOcupacion;
      document.getElementById('docCargoEmail').textContent = `${medico.Cargo || 'Médico'} • ${medico.Correo || 'DNI: ' + medico.NumeroDocumento}`;

      // Renderizar modulos autorizados
      const containerModulos = document.getElementById('containerModulosAutorizados');
      containerModulos.innerHTML = '';

      const modulos = medico.ModulosAutorizados || [];
      modulos.forEach(m => {
        const card = document.createElement('div');
        card.className = `module-card ${m.activo ? 'authorized' : 'restricted'}`;

        card.innerHTML = `
          <div>
            <div class="module-card-header">
              <span class="module-icon">${m.icono}</span>
              <div>
                <div class="module-name">${escapeHtml(m.nombre)}</div>
                <span style="font-size:10px; color:${m.activo ? '#0369a1' : '#64748b'}; font-weight:700;">${escapeHtml(m.badge)}</span>
              </div>
            </div>
            <div class="module-sub">${escapeHtml(m.subtitulo)}</div>
          </div>
          <div>
            <button type="button" class="btn-enter-module ${m.activo ? '' : 'disabled'}" ${m.activo ? `onclick="ingresarModulo('${m.id}')"` : 'disabled title="Módulo restringido para esta especialidad"'}>
              ${m.activo ? 'Ingresar a Ficha &rarr;' : '🔒 No Autorizado'}
            </button>
          </div>
        `;
        containerModulos.appendChild(card);
      });
    }

    function ingresarModulo(moduloId) {
      window.location.href = `index.php?modulo=${moduloId}`;
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }
  </script>
</body>
</html>
