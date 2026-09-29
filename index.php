<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laboratorio Clinico - Hospital Distrital Pacasmayo</title>
  <meta name="description" content="Sistema Web del Laboratorio de la UPSS Patologia Clinica y Anatomia Patologica del Hospital Distrital Pacasmayo">
  <link rel="stylesheet" href="assets/css/laboratorio.css">
</head>
<body>

  <!-- =========================================================
       BARRA SUPERIOR DE CABECERA INSTITUCIONAL
       ========================================================= -->
  <header class="top-navbar no-print">
    <div class="brand-wrapper">
      <img src="assets/img/logo_hospital.svg" alt="Hospital Distrital Pacasmayo" class="brand-icon">
      <div>
        <h1 class="brand-title">UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA</h1>
        <p class="brand-subtitle">Hospital Distrital Pacasmayo &bull; Red Integrada de Salud Pacasmayo</p>
      </div>
    </div>

    <div class="actions-bar">
      <!-- Badge de Médico Activo / Portal -->
      <div id="loggedDoctorContainer" style="display:flex; align-items:center; gap:8px;">
        <div id="doctorBadgeChip" style="display:flex; align-items:center; gap:6px; background:#e0f2fe; border:1px solid #bae6fd; padding:4px 10px; border-radius:20px; font-size:11.5px; color:#0369a1;">
          <span style="font-weight:700;" id="hdrDoctorName">🩺 Dr(a). No Identificado</span>
          <span class="badge" style="background:#0284c7; color:#fff; font-size:10px; padding:2px 6px; border-radius:10px;" id="hdrDoctorSpec">Médico</span>
        </div>
        <a href="portal.php" class="btn btn-outline" style="padding:5px 12px; font-size:11.5px; text-decoration:none; display:flex; align-items:center; gap:4px;" title="Cambiar médico o acceder al portal">
          <span>Portal Médico</span>
        </a>
      </div>

      <span class="badge badge-emitido" style="padding:6px 10px; font-size:11px;" title="Conexion nativa activa con SQL Server Express">
        <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
        BD_HOSPITAL ONLINE
      </span>

      <button type="button" id="btnToggleDarkMode" class="btn btn-outline" title="Alternar modo oscuro / claro">
        <svg id="iconDark" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg id="iconLight" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <span id="labelDarkMode">Modo Oscuro</span>
      </button>

      <button type="button" id="btnGlobalNuevo" class="btn btn-outline" title="Iniciar un nuevo analisis clinico en blanco">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Nuevo Analisis
      </button>

      <button type="button" id="btnGlobalImprimir" class="btn btn-primary" title="Imprimir formato oficial A4">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Imprimir
      </button>
    </div>
  </header>

  <!-- =========================================================
       MENU PRINCIPAL DE NAVEGACION (WORKSTATION TABS)
       ========================================================= -->
  <nav class="main-navbar no-print">
    <div class="nav-container">
      <button type="button" class="nav-btn" data-target="viewDashboard">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>Dashboard</span>
      </button>

      <!-- Dropdown: Formato de Laboratorio -->
      <div class="nav-dropdown" id="navDropdownFormato">
        <button type="button" class="nav-btn active" id="navBtnFormato" data-target="viewFormato">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          <span>Formato de Laboratorio</span>
          <svg class="dropdown-caret" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
        <div class="nav-dropdown-menu" id="navDropdownMenu">
          <button type="button" class="nav-dropdown-item active" data-formato="bioquimica" id="btnNavBioquimica">
            <span class="formato-icon formato-icon-bio">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"></path></svg>
            </span>
            <div>
              <div class="formato-item-title">Bioquimica</div>
              <div class="formato-item-sub">Perfil basal, lipidico, hepatico</div>
            </div>
          </button>
          <button type="button" class="nav-dropdown-item" data-formato="microbiologia" id="btnNavMicrobiologia">
            <span class="formato-icon formato-icon-micro">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"></path></svg>
            </span>
            <div>
              <div class="formato-item-title">Microbiologia</div>
              <div class="formato-item-sub">Cultivos, antibiogramas, Gram</div>
            </div>
          </button>
          <button type="button" class="nav-dropdown-item" data-formato="hematologia" id="btnNavHematologia">
            <span class="formato-icon formato-icon-hema">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </span>
            <div>
              <div class="formato-item-title">Hematologia</div>
              <div class="formato-item-sub">Hemograma, coagulacion, grupo</div>
            </div>
          </button>
        </div>
      </div>

      <button type="button" class="nav-btn" data-target="viewPacientes">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span>Buscar Pacientes</span>
      </button>

      <button type="button" class="nav-btn" data-target="viewReportes">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        <span>Reportes</span>
      </button>

      <button type="button" class="nav-btn" data-target="viewCatalogo">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
        <span>Valores de Referencia</span>
      </button>
    </div>
  </nav>

  <!-- =========================================================
       VISTA 1: ðŸ“Š DASHBOARD GENERAL
       ========================================================= -->
  <section id="viewDashboard" class="view-section no-print">
    <div class="container-boxed">
      <div class="dashboard-header">
        <div>
          <h2 class="dashboard-title">Panel General &bull; UPSS Laboratorio Clinico</h2>
          <p class="dashboard-subtitle">Hospital Distrital Pacasmayo &bull; Estado del servicio y estadisticas del dia</p>
        </div>
        <button type="button" id="btnRefreshDashboard" class="btn btn-secondary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
          Actualizar Metricas
        </button>
      </div>

      <!-- Tarjetas de Estadisticas -->
      <div class="stats-grid stats-grid-2">
        <div class="stat-card">
          <div class="stat-icon-wrapper stat-icon-primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          </div>
          <div class="stat-info">
            <div class="stat-value" id="statHoy">0</div>
            <div class="stat-label">Analisis de Hoy</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrapper stat-icon-info">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="stat-info">
            <div class="stat-value" id="statPacientes">0</div>
            <div class="stat-label">Pacientes en BD</div>
          </div>
        </div>
      </div>

      <!-- Accesos Rapidos -->
      <div class="quick-actions-bar">
        <div class="action-card" id="btnQuickFormato">
          <div class="action-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
          </div>
          <div>
            <div class="action-card-title">Llenar Formato de Laboratorio</div>
            <div class="action-card-desc">Iniciar captura de analisis clinico e impresion A4</div>
          </div>
        </div>

        <div class="action-card" id="btnQuickPacientes">
          <div class="action-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </div>
          <div>
            <div class="action-card-title">Buscar o Registrar Paciente</div>
            <div class="action-card-desc">Busqueda por DNI/HC o registrar nuevo en la BD</div>
          </div>
        </div>

        <div class="action-card" id="btnQuickReportes">
          <div class="action-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
          </div>
          <div>
            <div class="action-card-title">Modulo de Reportes</div>
            <div class="action-card-desc">Estadisticas por servicios, estados y valores alterados</div>
          </div>
        </div>
      </div>

      <!-- Paneles de Graficos Analiticos -->
      <div class="dashboard-charts-grid">
        <div class="card-panel">
          <div class="card-panel-header">
            <div class="card-panel-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
              Tendencia de Solicitudes (Ultimos 7 Dias)
            </div>
            <span class="badge badge-validado" style="font-size:11px;">Tiempo Real</span>
          </div>
          <div class="card-panel-body">
            <div class="chart-wrapper">
              <canvas id="chartTendencia"></canvas>
            </div>
          </div>
        </div>

        <div class="card-panel">
          <div class="card-panel-header">
            <div class="card-panel-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
              Distribucion de Solicitudes por Estado
            </div>
            <span class="badge badge-emitido" style="font-size:11px;">Flujo UPSS</span>
          </div>
          <div class="card-panel-body">
            <div class="chart-wrapper">
              <canvas id="chartEstados"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel Estado del Sistema + Demanda -->
      <div class="dashboard-split">

        <!-- Panel Semaforo de Estados -->
        <div class="card-panel">
          <div class="card-panel-header">
            <div class="card-panel-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              Estado del Sistema
            </div>
            <button type="button" class="btn btn-outline" style="padding:4px 10px; font-size:12px;" id="btnVerTodoHistorialDash">
              Ver Historial &rarr;
            </button>
          </div>
          <div class="card-panel-body">
            <div class="estado-semaforo">
              <div class="semaforo-item semaforo-pendiente">
                <div class="semaforo-luz"></div>
                <div class="semaforo-info">
                  <div class="semaforo-valor" id="statPendientesSem">0</div>
                  <div class="semaforo-label">Pendiente</div>
                  <div class="semaforo-desc">Solicitudes en espera de procesamiento o validacion</div>
                </div>
              </div>
              <div class="semaforo-item semaforo-anulado">
                <div class="semaforo-luz"></div>
                <div class="semaforo-info">
                  <div class="semaforo-valor" id="statAnuladosSem">0</div>
                  <div class="semaforo-label">Anulado</div>
                  <div class="semaforo-desc">Solicitudes canceladas o anuladas del sistema</div>
                </div>
              </div>
              <div class="semaforo-item semaforo-validado">
                <div class="semaforo-luz"></div>
                <div class="semaforo-info">
                  <div class="semaforo-valor" id="statValidadosSem">0</div>
                  <div class="semaforo-label">Validado</div>
                  <div class="semaforo-desc">Resultados validados y listos para entrega</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel Demanda por Servicio -->
        <div class="card-panel">
          <div class="card-panel-header">
            <div class="card-panel-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
              Demanda por Servicio Hospitalario
            </div>
          </div>
          <div class="card-panel-body">
            <div class="chart-wrapper" style="height:180px; margin-bottom:12px;">
              <canvas id="chartServicios"></canvas>
            </div>
            <div id="containerDemandaServicios">
              <!-- Barras generadas por JS -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================
       VISTA 2: ðŸ“ FORMATO OFICIAL DE LABORATORIO (LLENAR E IMPRIMIR)
       ========================================================= -->
  <section id="viewFormato" class="view-section active">
    <!-- ===== BARRA SELECTORA DE TIPO DE FORMATO ===== -->
    <div class="formato-type-bar no-print">
      <div class="formato-tabs">
        <button type="button" class="formato-tab active" data-formato="bioquimica" id="tabBioquimica">
          <span class="formato-tab-icon formato-icon-bio">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"></path></svg>
          </span>
          <div class="formato-tab-texts">
            <span class="formato-tab-title">Bioquimica</span>
            <span class="formato-tab-sub">Perfil basal, lipidico, hepatico</span>
          </div>
        </button>
        <button type="button" class="formato-tab" data-formato="microbiologia" id="tabMicrobiologia">
          <span class="formato-tab-icon formato-icon-micro">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"></path></svg>
          </span>
          <div class="formato-tab-texts">
            <span class="formato-tab-title">Microbiologia</span>
            <span class="formato-tab-sub">Cultivos, antibiogramas, Gram</span>
          </div>
        </button>
        <button type="button" class="formato-tab" data-formato="hematologia" id="tabHematologia">
          <span class="formato-tab-icon formato-icon-hema">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </span>
          <div class="formato-tab-texts">
            <span class="formato-tab-title">Hematologia</span>
            <span class="formato-tab-sub">Hemograma, coagulacion, grupo</span>
          </div>
        </button>
      </div>

      <!-- Acciones -->
      <div class="actions-bar">
        <button type="button" id="btnNuevo" class="btn btn-outline" title="Iniciar un nuevo analisis en blanco">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
          Nuevo
        </button>
        <button type="button" id="btnDemo" class="btn btn-secondary" title="Cargar paciente de prueba desde SQL Server">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
          Demo
        </button>
        <button type="button" id="btnGuardar" class="btn btn-success" title="Guardar analisis y resultados en SQL Server">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Guardar en BD
        </button>
        <button type="button" id="btnImprimir" class="btn btn-primary" title="Imprimir formato oficial A4 identico al fisico">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
          Imprimir Ficha Oficial
        </button>
      </div>
    </div>

    <!-- ======= PANEL BIOQUIMICA (ACTIVO) ======= -->
    <div id="panelBioquimica" class="formato-panel active">
    <!-- Contenedor de la Hoja Oficial Replica Fisica -->
    <main class="app-container">

      <!-- Banner de Modo Edicion -->
      <div id="bannerModoEdicion" class="edit-mode-banner no-print" style="display:none;">
        <div>
          <span>✏️ <strong>Modo Edición:</strong> Editando Solicitud <strong id="lblEditCodigo">-</strong></span>
          <span style="color:#78350f; font-size:12px; margin-left:8px;">(Modifique los campos y presione "Guardar en BD" para actualizar)</span>
        </div>
        <button type="button" class="btn btn-outline" id="btnCancelarEdicion" style="padding:4px 12px; font-size:12px; background:#ffffff; color:#92400e; border-color:#d97706;">
          ✕ Cancelar Edición
        </button>
      </div>

      <article class="lab-sheet" id="printSheet">

        <!-- ENCABEZADO INSTITUCIONAL -->
        <header class="sheet-header">
          <div class="header-logo-left">
            <img src="assets/img/logo_ris.svg" alt="Escudo Red Integrada de Salud Pacasmayo">
          </div>
          <div class="header-titles">
            <div class="title-gov">GOBIERNO REGIONAL DE LA LIBERTAD</div>
            <div class="title-red">RED INTEGRADA DE SALUD PACASMAYO</div>
            <div class="title-hosp">HOSPITAL DISTRITAL PACASMAYO</div>
            <div class="title-unit">UPSS PATOLOGÍA CLÍNICA</div>
            <div class="title-doc">ANALISIS BIOQUIMICO</div>
          </div>
          <div class="header-logo-right">
            <img src="assets/img/logo_hospital.svg" alt="Logo Hospital Distrital Pacasmayo">
          </div>
        </header>

        <!-- TABLA DE DATOS DEL PACIENTE Y ORDEN -->
        <table class="patient-box">
          <tbody>
            <!-- Fila 1: Busqueda de Paciente con Autocompletado -->
            <tr class="no-print">
              <td class="label" style="width: 18%; background:#e0f2fe; color:#0369a1;">BUSCAR PACIENTE:</td>
              <td colspan="7" style="background:#f0f9ff; padding: 6px 10px;">
                  <div class="patient-search-wrapper" id="patientSearchWrapper">
                    <svg class="patient-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="txtBuscarPaciente" class="patient-search-input" placeholder="Escriba apellidos, nombres o DNI del paciente..." autocomplete="off" spellcheck="false">
                    <button type="button" id="btnLimpiarPaciente" class="patient-search-clear" title="Limpiar seleccion" style="display:none;">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                    <div id="patientAutocomplete" class="autocomplete-dropdown no-print"></div>
                  </div>
              </td>
            </tr>

            <!-- Fila 2: Apellidos y Nombres Separados -->
            <tr>
              <td class="label" style="width: 18%;">APELLIDOS:</td>
              <td colspan="3" style="width: 32%;">
                <input type="text" id="txtApellidos" class="patient-input" placeholder="Apellido Paterno y Materno" autocomplete="off">
              </td>
              <td class="label" style="width: 18%;">NOMBRES:</td>
              <td colspan="3" style="width: 32%;">
                <input type="text" id="txtNombres" class="patient-input" placeholder="Nombres del paciente" autocomplete="off">
              </td>
            </tr>

            <!-- Fila 3: Sexo, Edad e Historia Clinica -->
            <tr>
              <td class="label">SEXO:</td>
              <td style="width: 16%;">
                <div class="sexo-radios">
                  <label><input type="radio" name="sexo" id="sexoM" value="M" checked> M</label>
                  <label><input type="radio" name="sexo" id="sexoF" value="F"> F</label>
                </div>
              </td>
              <td class="label" style="width: 10%;">EDAD:</td>
              <td style="width: 12%;">
                <input type="number" id="txtEdad" class="patient-input" placeholder="Años" min="0" max="130">
              </td>
              <td class="label" style="width: 18%;">HISTORIA CLINICA:</td>
              <td colspan="3">
                <input type="text" id="txtHistoriaClinica" class="patient-input" placeholder="NÂ° Historia Clinica">
              </td>
            </tr>

            <!-- Fila 4: DNI y Fecha de Toma -->
            <tr>
              <td class="label">DNI:</td>
              <td colspan="3">
                <input type="text" id="txtDni" class="patient-input" placeholder="NÂ° Documento Nacional de Identidad" maxlength="15">
              </td>
              <td class="label">FECHA Y HORA DE TOMA DE MUESTRA:</td>
              <td colspan="3">
                <input type="datetime-local" id="txtFechaToma" class="patient-input">
              </td>
            </tr>

            <!-- Fila 5: Servicio (Combobox escribible) y Fecha de Emision -->
            <tr>
              <td class="label">SERVICIO:</td>
              <td colspan="3">
                <input type="text" list="listaServicios" id="txtServicio" class="patient-input" placeholder="Seleccione o escriba el servicio..." autocomplete="off">
                <datalist id="listaServicios">
                  <option value="UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA" data-id="1">
                  <option value="MEDICINA GENERAL" data-id="2">
                  <option value="PEDIATRIA" data-id="3">
                  <option value="GINECO-OBSTETRICIA" data-id="4">
                  <option value="EMERGENCIA" data-id="5">
                  <option value="HOSPITALIZACION" data-id="6">
                </datalist>
              </td>
              <td class="label">FECHA DE EMISION DE RESULTADOS:</td>
              <td colspan="3">
                <input type="datetime-local" id="txtFechaEmision" class="patient-input">
              </td>
            </tr>

            <!-- Fila 6: Medico Solicitante (Combobox escribible) y Estado -->
            <tr>
              <td class="label">MEDICO SOLICITANTE:</td>
              <td colspan="3">
                <input type="text" list="listaMedicos" id="txtMedico" class="patient-input" placeholder="Seleccione o escriba el medico solicitante..." autocomplete="off">
                <datalist id="listaMedicos">
                  <option value="Morales Solis, Ricardo" data-id="1">
                </datalist>
              </td>
              <td class="label">ESTADO DE SOLICITUD:</td>
              <td colspan="3">
                <select id="cboEstadoSolicitud" class="patient-input" style="font-weight:700; color:#0284c7; cursor:pointer;">
                  <option value="VALIDADO" selected>VALIDADO</option>
                  <option value="PENDIENTE">PENDIENTE</option>
                  <option value="ANULADO">ANULADO</option>
                </select>
              </td>
            </tr>

            <!-- Fila 7: Diagnostico Presuntivo -->
            <tr>
              <td class="label">DIAGNOSTICO PRESUNTIVO:</td>
              <td colspan="7">
                <input type="text" id="txtDiagnostico" class="patient-input" placeholder="Codigo CIE-10 o descripcion clinica del diagnostico presuntivo">
              </td>
            </tr>
          </tbody>
        </table>

        <!-- =========================================================
             GRID DE DOS COLUMNAS DE EXAMENES (FORMATO EXACTO)
             ========================================================= -->
        <div class="exam-grid-container">

          <!-- ================= COLUMNA IZQUIERDA ================= -->
          <div class="grid-col">

            <!-- 1. PERFIL BASAL -->
            <table class="exam-table">
              <thead>
                <tr class="section-header-row">
                  <th colspan="4">PERFIL BASAL</th>
                </tr>
                <tr class="col-header-row">
                  <th style="width: 38%;">EXAMEN</th>
                  <th style="width: 20%;">RESULTADOS</th>
                  <th style="width: 14%;">UNIDADES</th>
                  <th style="width: 28%;">VALORES DE REFERENCIA</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="param-name-cell">GLUCOSA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="29" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">70 - 110 mg/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">UREA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="30" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">15 - 45 mg/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">CREATININA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="31" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>Hombres: 0.9 - 1.3 mg/dl</div>
                    <div>Mujeres: 0.6 - 1.1 mg/dl</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">ACIDO URICO</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="32" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>Hombres 2.5 - 7.2 mg/dl</div>
                    <div>Mujeres: 2.5 - 6.0 mg/dl</div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- 2. PERFIL LIPIDICO -->
            <table class="exam-table">
              <thead>
                <tr class="section-header-row">
                  <th colspan="4">PERFIL LIPIDICO</th>
                </tr>
                <tr class="col-header-row">
                  <th style="width: 38%;">EXAMEN</th>
                  <th style="width: 20%;">RESULTADOS</th>
                  <th style="width: 14%;">UNIDADES</th>
                  <th style="width: 28%;">VALORES DE REFERENCIA</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="param-name-cell">COLESTEROL TOTAL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="33" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">&lt; 200 mg/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">HDL - COLESTEROL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="34" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>Alto riesgo: &lt; 40 mg/dl</div>
                    <div>Riesgo moderado: 40 - 59 mg/dl</div>
                    <div>Sin Riesgo: &ge; 60 mg/dl</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">LDL - COLESTEROL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="35" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>Optimo: &lt; 100 mg/dl</div>
                    <div>Elevado: 100 - 189 mg/dl</div>
                    <div>Muy alto &ge; 190 mg/dl</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">VLDL - COLESTEROL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="36" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>Optimo: &lt; 150 mg/dl</div>
                    <div>Elevado: 150 - 499 mg/dl</div>
                    <div>Muy alto &ge; 500 mg/dl</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">TRIGLICERIDOS</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="37" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>Optimo: &lt; 150 mg/dl</div>
                    <div>Elevado: 150 - 499 mg/dl</div>
                    <div>Muy alto &ge; 500 mg/dl</div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- 3. OTROS EXAMENES -->
            <table class="exam-table">
              <thead>
                <tr class="section-header-row">
                  <th colspan="4">OTROS EXAMENES</th>
                </tr>
                <tr class="col-header-row">
                  <th style="width: 38%;">EXAMEN</th>
                  <th style="width: 20%;">RESULTADOS</th>
                  <th style="width: 14%;">UNIDADES</th>
                  <th style="width: 28%;">VALORES DE REFERENCIA</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="param-name-cell">GLUCOSA POST PANDRIAL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="53" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>Normal: &lt; 140 mg/dl</div>
                    <div>Intolerancia: 140 - 200 mg/dl</div>
                    <div>diabetes mellitus: &gt; 200 mg/dl</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">HEMOGLOBINA GLICOSILADA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="54" placeholder="---">
                  </td>
                  <td class="param-unit-cell">%</td>
                  <td class="param-ref-cell">
                    <div>Normal: 4.0 - 5.7 %</div>
                    <div>Estable: 5.7 - 6.4 %</div>
                    <div>Elevado: &ge; 6.5 %</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">AMILASA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="55" placeholder="---">
                  </td>
                  <td class="param-unit-cell">U/L</td>
                  <td class="param-ref-cell">&lt; 125 U/L</td>
                </tr>
                <tr>
                  <td class="param-name-cell">LIPASA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="56" placeholder="---">
                  </td>
                  <td class="param-unit-cell">U/L</td>
                  <td class="param-ref-cell">13 - 60 U/L</td>
                </tr>
              </tbody>
            </table>

          </div>

          <!-- ================= COLUMNA DERECHA ================= -->
          <div class="grid-col">

            <!-- 4. PERFIL HEPATICO -->
            <table class="exam-table">
              <thead>
                <tr class="section-header-row">
                  <th colspan="4">PERFIL HEPATICO</th>
                </tr>
                <tr class="col-header-row">
                  <th style="width: 38%;">EXAMEN</th>
                  <th style="width: 18%;">RESULTADOS</th>
                  <th style="width: 14%;">UNIDADES</th>
                  <th style="width: 30%;">VALORES DE REFERENCIA</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="param-name-cell">PROTEINA TOTAL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="38" placeholder="---">
                  </td>
                  <td class="param-unit-cell">g/dl</td>
                  <td class="param-ref-cell">6.1 - 7.9 g/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">ALBUMINA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="39" placeholder="---">
                  </td>
                  <td class="param-unit-cell">g/dl</td>
                  <td class="param-ref-cell">3.5 - 4.8 g/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">GLOBULINA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="40" placeholder="---">
                  </td>
                  <td class="param-unit-cell">g/dl</td>
                  <td class="param-ref-cell">1.2 - 2.2 g/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">TRANSAMINASA TGO</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="41" placeholder="---">
                  </td>
                  <td class="param-unit-cell">U/L</td>
                  <td class="param-ref-cell">
                    <div>Hombres: &lt; 38 U/L</div>
                    <div>Mujeres: &lt; 32 U/L</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">TRANSAMINASA TGP</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="42" placeholder="---">
                  </td>
                  <td class="param-unit-cell">U/L</td>
                  <td class="param-ref-cell">
                    <div>Hombres: &lt; 41 U/L</div>
                    <div>Mujeres: &lt; 31 U/L</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">BILIRRUBINA TOTAL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="43" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">
                    <div>&lt; 1.0 mg/dl</div>
                    <table class="sub-table-bili">
                      <thead>
                        <tr>
                          <th></th>
                          <th>Nacidos a Termino</th>
                          <th>Prematuros</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td style="text-align:left;">Sangre de cordon:</td>
                          <td>2.5 mg/dl</td>
                          <td>-</td>
                        </tr>
                        <tr>
                          <td style="text-align:left;">Hasta las 24 horas:</td>
                          <td>6.0 mg/dl</td>
                          <td>8.0 mg/dl</td>
                        </tr>
                        <tr>
                          <td style="text-align:left;">Hasta las 48 horas:</td>
                          <td>7.5 mg/dl</td>
                          <td>12.0 mg/dl</td>
                        </tr>
                        <tr>
                          <td style="text-align:left;">De 2 a 5 dias:</td>
                          <td>12.0 mg/dl</td>
                          <td>24.0 mg/dl</td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">BILIRRUBINA DIRECTA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="44" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">&lt; 0.2 mg/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">BILIRRUBINA INDIRECTA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="45" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">&lt; 0.8 mg/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">FOSFATASA ALCALINA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="46" placeholder="---">
                  </td>
                  <td class="param-unit-cell">U/L</td>
                  <td class="param-ref-cell">
                    <div>Entre 20 - 60 años: 65 - 300 U/L</div>
                    <div>Niños y adolescentes: &lt; 645 U/L</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">GAMMA GLUTAMIL TRANSPERASA (GGT)</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="47" placeholder="---">
                  </td>
                  <td class="param-unit-cell">U/L</td>
                  <td class="param-ref-cell">
                    <div>Hombres: &lt; 60 U/L</div>
                    <div>Mujeres: &lt; 40 U/L</div>
                  </td>
                </tr>
                <tr>
                  <td class="param-name-cell">TIEMPO DE PROTOMBINA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="48" placeholder="---">
                  </td>
                  <td class="param-unit-cell">seg.</td>
                  <td class="param-ref-cell">12 - 14 seg</td>
                </tr>
                <tr>
                  <td class="param-name-cell">INR</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="49" placeholder="---">
                  </td>
                  <td class="param-unit-cell">%</td>
                  <td class="param-ref-cell">1.0 - 2.0 %</td>
                </tr>
              </tbody>
            </table>

            <!-- 5. TOLERANCIA A LA GLUCOSA -->
            <table class="exam-table">
              <thead>
                <tr class="section-header-row">
                  <th colspan="4">TOLERANCIA A LA GLUCOSA</th>
                </tr>
                <tr class="col-header-row">
                  <th style="width: 38%;">EXAMEN</th>
                  <th style="width: 18%;">RESULTADOS</th>
                  <th style="width: 14%;">UNIDADES</th>
                  <th style="width: 30%;">VALORES DE REFERENCIA</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="param-name-cell">GLUCOSA BASAL</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="50" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">70 - 110 mg/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">GLUCOSA 1 HORA</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="51" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">&lt; 140 mg/dl</td>
                </tr>
                <tr>
                  <td class="param-name-cell">GLUCOSA 2 HORAS</td>
                  <td class="param-result-cell">
                    <input type="text" class="res-input" data-param-id="52" placeholder="---">
                  </td>
                  <td class="param-unit-cell">mg/dl</td>
                  <td class="param-ref-cell">&lt; 200 mg/dl</td>
                </tr>
              </tbody>
            </table>

            <!-- 6. OTROS (TEXTO LIBRE) -->
            <table class="notes-table">
              <thead>
                <tr class="section-header-row">
                  <th>OTROS</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <textarea id="txtOtrosExamenes" class="notes-textarea" placeholder="Especifique otros examenes analizados o datos complementarios..."></textarea>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- 7. OBSERVACIONES -->
            <table class="notes-table">
              <thead>
                <tr class="section-header-row">
                  <th>OBSERVACIONES</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <textarea id="txtObservaciones" class="notes-textarea" placeholder="Observaciones tecnicas, metodologias aplicadas o notas clinicas del analisis..."></textarea>
                  </td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>

        <!-- PIE DE FIRMA Y VALIDACION DE RESULTADOS -->
        <footer class="sheet-footer">
          <div class="footer-disclaimer">
            <p><strong>Hospital Distrital Pacasmayo</strong> &bull; UPSS Patologia Clinica y Anatomia Patologica</p>
            <p>Documento oficial emitido conforme a las normas tecnicas de salud y control de calidad institucional.</p>
            <p style="margin-top:2px;">La validez de este resultado esta sujeta a la correlacion clinica del medico tratante.</p>
          </div>

          <div class="signature-box">
            <div class="signature-stamp-area">
              <div class="stamp-digital">
                <span>VALIDACION DIGITAL</span>
                <span>UPSS LABORATORIO CLINICO</span>
              </div>
            </div>
            <div class="signature-title">RESPONSABLE DE LABORATORIO</div>
            <div class="signature-subtitle">Tecnologo Medico / Patologo Clinico</div>
          </div>
        </footer>

      </article>
    </main>
    </div><!-- /panelBioquimica -->

    <!-- ======= PANEL MICROBIOLOGIA ======= -->
    <div id="panelMicrobiologia" class="formato-panel">
      <main class="app-container">
        <article class="lab-sheet" id="printSheetMicro">

          <!-- ENCABEZADO INSTITUCIONAL -->
          <header class="sheet-header">
            <div class="header-logo-left">
              <img src="assets/img/logo_ris.svg" alt="Escudo Red Integrada de Salud Pacasmayo">
            </div>
            <div class="header-titles">
              <div class="title-gov">GOBIERNO REGIONAL DE LA LIBERTAD</div>
              <div class="title-red">RED INTEGRADA DE SALUD PACASMAYO</div>
              <div class="title-upss">UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA DEL HOSPITAL DISTRITAL PACASMAYO</div>
            </div>
            <div class="header-logo-right">
              <img src="assets/img/logo_hospital.svg" alt="Logo Hospital Distrital Pacasmayo">
            </div>
          </header>

          <!-- Subtitulo del formato -->
          <div class="formato-subtitulo-banner formato-banner-micro">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"></path></svg>
            FORMATO DE MICROBIOLOGIA
          </div>

          <!-- DATOS DEL PACIENTE -->
          <table class="patient-box">
            <tbody>
              <tr>
                <td class="label" style="width: 18%;">APELLIDOS Y NOMBRES:</td>
                <td colspan="7">
                  <input type="text" id="microTxtApellidosNombres" class="patient-input" placeholder="Escriba apellidos, nombres o DNI para autocompletar desde la BD..." autocomplete="off">
                </td>
              </tr>
              <tr>
                <td class="label">SEXO:</td>
                <td style="width: 16%;">
                  <div class="sexo-radios">
                    <label><input type="radio" name="microSexo" value="M" checked> M</label>
                    <label><input type="radio" name="microSexo" value="F"> F</label>
                  </div>
                </td>
                <td class="label" style="width: 10%;">EDAD:</td>
                <td style="width: 12%;">
                  <input type="number" id="microTxtEdad" class="patient-input" placeholder="A&ntilde;os" min="0" max="130">
                </td>
                <td class="label" style="width: 18%;">HISTORIA CLINICA:</td>
                <td colspan="3">
                  <input type="text" id="microTxtHistoriaClinica" class="patient-input" placeholder="N&ordm; Historia Clinica">
                </td>
              </tr>
              <tr>
                <td class="label">DNI:</td>
                <td colspan="3">
                  <input type="text" id="microTxtDni" class="patient-input" placeholder="N&ordm; Documento Nacional de Identidad" maxlength="15">
                </td>
                <td class="label">FECHA Y HORA DE TOMA DE MUESTRA:</td>
                <td colspan="3">
                  <input type="datetime-local" id="microTxtFechaToma" class="patient-input">
                </td>
              </tr>
              <tr>
                <td class="label">SERVICIO:</td>
                <td colspan="3">
                  <select id="microCboServicio" class="patient-input" style="cursor:pointer;">
                    <option value="1">UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA</option>
                    <option value="2">MEDICINA GENERAL</option>
                    <option value="3">PEDIATRIA</option>
                    <option value="4">GINECO-OBSTETRICIA</option>
                    <option value="5">EMERGENCIA</option>
                    <option value="6">HOSPITALIZACION</option>
                  </select>
                </td>
                <td class="label">FECHA DE EMISION DE RESULTADOS:</td>
                <td colspan="3">
                  <input type="datetime-local" id="microTxtFechaEmision" class="patient-input">
                </td>
              </tr>
              <tr>
                <td class="label">MEDICO SOLICITANTE:</td>
                <td colspan="7">
                  <input type="text" id="microTxtMedico" class="patient-input" placeholder="Nombre del medico solicitante">
                </td>
              </tr>
            </tbody>
          </table>

          <!-- AREA DE CONTENIDO (PROXIMAMENTE) -->
          <div class="formato-coming-soon-wrapper">
            <div class="formato-coming-soon">
              <div class="fcs-icon fcs-icon-micro">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"></path></svg>
              </div>
              <h3>Formato de Microbiologia</h3>
              <p>Esta seccion esta lista para recibir el formato oficial de Microbiologia.<br>
              Incluira: cultivos bacterianos, antibiograma, tincion Gram, pruebas de sensibilidad y morfologia colonial.</p>
              <div class="fcs-tags">
                <span class="fcs-tag">Cultivos</span>
                <span class="fcs-tag">Antibiograma</span>
                <span class="fcs-tag">Tincion Gram</span>
                <span class="fcs-tag">Sensibilidad</span>
                <span class="fcs-tag">Morfologia Colonial</span>
              </div>
              <div class="fcs-badge fcs-badge-micro">Proximamente</div>
            </div>
          </div>

          <!-- PIE DEL FORMATO -->
          <footer class="sheet-footer">
            <div class="footer-disclaimer">
              <p><strong>Hospital Distrital Pacasmayo</strong> &bull; UPSS Patologia Clinica y Anatomia Patologica</p>
              <p>Documento oficial emitido conforme a las normas tecnicas de salud y control de calidad institucional.</p>
              <p style="margin-top:2px;">La validez de este resultado esta sujeta a la correlacion clinica del medico tratante.</p>
            </div>
            <div class="signature-box">
              <div class="signature-stamp-area">
                <div class="stamp-digital">
                  <span>VALIDACION DIGITAL</span>
                  <span>UPSS LABORATORIO CLINICO</span>
                </div>
              </div>
              <div class="signature-title">RESPONSABLE DE LABORATORIO</div>
              <div class="signature-subtitle">Tecnologo Medico / Patologo Clinico</div>
            </div>
          </footer>

        </article>
      </main>
    </div><!-- /panelMicrobiologia -->

    <!-- ======= PANEL HEMATOLOGIA ======= -->
    <div id="panelHematologia" class="formato-panel">
      <main class="app-container">
        <article class="lab-sheet" id="printSheetHema">

          <!-- ENCABEZADO INSTITUCIONAL -->
          <header class="sheet-header">
            <div class="header-logo-left">
              <img src="assets/img/logo_ris.svg" alt="Escudo Red Integrada de Salud Pacasmayo">
            </div>
            <div class="header-titles">
              <div class="title-gov">GOBIERNO REGIONAL DE LA LIBERTAD</div>
              <div class="title-red">RED INTEGRADA DE SALUD PACASMAYO</div>
              <div class="title-upss">UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA DEL HOSPITAL DISTRITAL PACASMAYO</div>
            </div>
            <div class="header-logo-right">
              <img src="assets/img/logo_hospital.svg" alt="Logo Hospital Distrital Pacasmayo">
            </div>
          </header>

          <!-- Subtitulo del formato -->
          <div class="formato-subtitulo-banner formato-banner-hema">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            FORMATO DE HEMATOLOGIA
          </div>

          <!-- DATOS DEL PACIENTE -->
          <table class="patient-box">
            <tbody>
              <tr>
                <td class="label" style="width: 18%;">APELLIDOS Y NOMBRES:</td>
                <td colspan="7">
                  <input type="text" id="hemaTxtApellidosNombres" class="patient-input" placeholder="Escriba apellidos, nombres o DNI para autocompletar desde la BD..." autocomplete="off">
                </td>
              </tr>
              <tr>
                <td class="label">SEXO:</td>
                <td style="width: 16%;">
                  <div class="sexo-radios">
                    <label><input type="radio" name="hemaSexo" value="M" checked> M</label>
                    <label><input type="radio" name="hemaSexo" value="F"> F</label>
                  </div>
                </td>
                <td class="label" style="width: 10%;">EDAD:</td>
                <td style="width: 12%;">
                  <input type="number" id="hemaTxtEdad" class="patient-input" placeholder="A&ntilde;os" min="0" max="130">
                </td>
                <td class="label" style="width: 18%;">HISTORIA CLINICA:</td>
                <td colspan="3">
                  <input type="text" id="hemaTxtHistoriaClinica" class="patient-input" placeholder="N&ordm; Historia Clinica">
                </td>
              </tr>
              <tr>
                <td class="label">DNI:</td>
                <td colspan="3">
                  <input type="text" id="hemaTxtDni" class="patient-input" placeholder="N&ordm; Documento Nacional de Identidad" maxlength="15">
                </td>
                <td class="label">FECHA Y HORA DE TOMA DE MUESTRA:</td>
                <td colspan="3">
                  <input type="datetime-local" id="hemaTxtFechaToma" class="patient-input">
                </td>
              </tr>
              <tr>
                <td class="label">SERVICIO:</td>
                <td colspan="3">
                  <select id="hemaCboServicio" class="patient-input" style="cursor:pointer;">
                    <option value="1">UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA</option>
                    <option value="2">MEDICINA GENERAL</option>
                    <option value="3">PEDIATRIA</option>
                    <option value="4">GINECO-OBSTETRICIA</option>
                    <option value="5">EMERGENCIA</option>
                    <option value="6">HOSPITALIZACION</option>
                  </select>
                </td>
                <td class="label">FECHA DE EMISION DE RESULTADOS:</td>
                <td colspan="3">
                  <input type="datetime-local" id="hemaTxtFechaEmision" class="patient-input">
                </td>
              </tr>
              <tr>
                <td class="label">MEDICO SOLICITANTE:</td>
                <td colspan="7">
                  <input type="text" id="hemaTxtMedico" class="patient-input" placeholder="Nombre del medico solicitante">
                </td>
              </tr>
            </tbody>
          </table>

          <!-- AREA DE CONTENIDO (PROXIMAMENTE) -->
          <div class="formato-coming-soon-wrapper">
            <div class="formato-coming-soon">
              <div class="fcs-icon fcs-icon-hema">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              </div>
              <h3>Formato de Hematologia</h3>
              <p>Esta seccion esta lista para recibir el formato oficial de Hematologia.<br>
              Incluira: hemograma completo, recuento diferencial de leucocitos, plaquetas, tiempo de coagulacion y grupo sanguineo.</p>
              <div class="fcs-tags">
                <span class="fcs-tag">Hemograma</span>
                <span class="fcs-tag">Leucocitos</span>
                <span class="fcs-tag">Plaquetas</span>
                <span class="fcs-tag">Coagulacion</span>
                <span class="fcs-tag">Grupo Sanguineo</span>
              </div>
              <div class="fcs-badge fcs-badge-hema">Proximamente</div>
            </div>
          </div>

          <!-- PIE DEL FORMATO -->
          <footer class="sheet-footer">
            <div class="footer-disclaimer">
              <p><strong>Hospital Distrital Pacasmayo</strong> &bull; UPSS Patologia Clinica y Anatomia Patologica</p>
              <p>Documento oficial emitido conforme a las normas tecnicas de salud y control de calidad institucional.</p>
              <p style="margin-top:2px;">La validez de este resultado esta sujeta a la correlacion clinica del medico tratante.</p>
            </div>
            <div class="signature-box">
              <div class="signature-stamp-area">
                <div class="stamp-digital">
                  <span>VALIDACION DIGITAL</span>
                  <span>UPSS LABORATORIO CLINICO</span>
                </div>
              </div>
              <div class="signature-title">RESPONSABLE DE LABORATORIO</div>
              <div class="signature-subtitle">Tecnologo Medico / Patologo Clinico</div>
            </div>
          </footer>

        </article>
      </main>
    </div><!-- /panelHematologia -->

  </section>

  <!-- =========================================================
       VISTA 3: ðŸ‘¥ BUSCAR Y DIRECTORIO DE PACIENTES
       ========================================================= -->
  <section id="viewPacientes" class="view-section no-print">
    <div class="container-boxed">
      <div class="dashboard-header">
        <div>
          <h2 class="dashboard-title">Directorio & Busqueda de Pacientes</h2>
          <p class="dashboard-subtitle">Busqueda rapida por DNI, NÂ° de Historia Clinica, Nombres o Apellidos en la BD Institucional</p>
        </div>
        <button type="button" id="btnAbrirModalNuevoPaciente" class="btn btn-primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
          + Registrar Nuevo Paciente
        </button>
      </div>

      <!-- Barra de Busqueda -->
      <div class="patients-header-bar">
        <div class="patient-search-input-wrapper">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" id="txtDirectorioSearch" placeholder="Escriba DNI, NÂ° Historia Clinica, Nombres o Apellidos del paciente..." autocomplete="off">
        </div>
      </div>

      <!-- Layout 2 Columnas -->
      <div class="patients-layout">
        <!-- Columna 1: Lista de Pacientes -->
        <div class="card-panel">
          <div class="card-panel-header">
            <div class="card-panel-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
              Pacientes (<span id="lblTotalPacientesEncontrados">0</span>)
            </div>
          </div>
          <div class="card-panel-body patient-list-container" id="containerListaPacientes">
            <div style="text-align:center; padding:30px; color:#94a3b8;">Cargando pacientes de BD_HOSPITAL...</div>
          </div>
        </div>

        <!-- Columna 2: Ficha Medica y su Historial -->
        <div>
          <div class="patient-dossier" id="panelFichaPaciente">
            <div style="text-align:center; padding:60px 20px; color:#94a3b8;">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom:12px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
              <h3 style="font-size:16px; color:#475569; font-weight:700;">Ningun Paciente Seleccionado</h3>
              <p style="font-size:13px; margin-top:4px;">Seleccione un paciente del directorio para ver su ficha completa e historial de analisis.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================
       VISTA 4: ðŸ“ˆ REPORTES Y ESTADISTICAS
       ========================================================= -->
  <section id="viewReportes" class="view-section no-print">
    <div class="container-boxed">
      <div class="dashboard-header">
        <div>
          <h2 class="dashboard-title">Reportes & Estadisticas de Laboratorio</h2>
          <p class="dashboard-subtitle">Consolidados de produccion, analisis por servicio y alertas clinicas</p>
        </div>
        <div style="display:flex; gap:8px;">
          <button type="button" id="btnExportarReporteCSV" class="btn btn-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Exportar CSV
          </button>
          <button type="button" id="btnImprimirReporte" class="btn btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Imprimir Reporte
          </button>
        </div>
      </div>

      <!-- Tarjeta de Filtros -->
      <div class="report-filters-card">
        <div class="filters-grid">
          <div class="filter-group">
            <label class="filter-label">Fecha Inicio:</label>
            <input type="date" id="repFechaInicio" class="filter-control">
          </div>
          <div class="filter-group">
            <label class="filter-label">Fecha Fin:</label>
            <input type="date" id="repFechaFin" class="filter-control">
          </div>
          <div class="filter-group">
            <label class="filter-label">Servicio Solicitante:</label>
            <select id="repServicio" class="filter-control">
              <option value="0">Todos los Servicios</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label">Estado:</label>
            <select id="repEstado" class="filter-control">
              <option value="TODOS">Todos los Estados</option>
              <option value="PENDIENTE">Pendientes</option>
              <option value="VALIDADO">Validados</option>
              <option value="ANULADO">Anulados</option>
            </select>
          </div>
          <div>
            <button type="button" id="btnFiltrarReporte" class="btn btn-primary" style="height:38px; width:100%; justify-content:center;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              Filtrar
            </button>
          </div>
        </div>

        <div class="date-presets">
          <button type="button" class="btn-preset" onclick="setReportDatePreset('hoy')">Hoy</button>
          <button type="button" class="btn-preset" onclick="setReportDatePreset('semana')">Esta Semana</button>
          <button type="button" class="btn-preset" onclick="setReportDatePreset('mes')">Este Mes</button>
          <button type="button" class="btn-preset" onclick="setReportDatePreset('anio')">Este Año</button>
        </div>
      </div>

      <!-- KPIs del Reporte -->
      <div class="report-summary-bar">
        <div class="report-kpi">
          <div class="report-kpi-val" id="repTotalAnalisis">0</div>
          <div class="report-kpi-lbl">Total Análisis</div>
        </div>
        <div class="report-kpi">
          <div class="report-kpi-val" id="repValidados" style="color:#2563eb;">0</div>
          <div class="report-kpi-lbl">Validados</div>
        </div>
        <div class="report-kpi">
          <div class="report-kpi-val" id="repPendientes" style="color:#d97706;">0</div>
          <div class="report-kpi-lbl">Pendientes</div>
        </div>
        <div class="report-kpi">
          <div class="report-kpi-val" id="repAnulados" style="color:#dc2626;">0</div>
          <div class="report-kpi-lbl">Anulados</div>
        </div>
      </div>

      <!-- Tabla Consolidada -->
      <div class="card-panel">
        <div class="card-panel-header">
          <div class="card-panel-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            Detalle Consolidado de Análisis en el Periodo
          </div>
        </div>
        <div style="overflow-x:auto;">
          <table class="modern-table" id="tablaReporteDetalle">
            <thead>
              <tr>
                <th>Código</th>
                <th>Fecha Solicitud</th>
                <th>Paciente</th>
                <th>DNI</th>
                <th>Historia Clínica</th>
                <th>Servicio</th>
                <th>Médico Solicitante</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyReporte">
              <tr><td colspan="9" style="text-align:center; padding:20px; color:#94a3b8;">Seleccione filtros y haga clic en Generar Reporte</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- =========================================================
       VISTA 6: ðŸ”¬ CATALOGO DE VALORES DE REFERENCIA
       ========================================================= -->
  <section id="viewCatalogo" class="view-section no-print">
    <div class="container-boxed">
      <div class="dashboard-header">
        <div>
          <h2 class="dashboard-title">Catalogo de Parametros y Valores de Referencia</h2>
          <p class="dashboard-subtitle">Especificaciones oficiales y rangos clinicos de la UPSS Patologia Clinica en BD_HOSPITAL</p>
        </div>
      </div>
      <div class="catalog-grid" id="containerCatalogo">
        <!-- Tarjetas dinamicas generadas por JS -->
      </div>
    </div>
  </section>

  <!-- =========================================================
       MODAL: REGISTRAR NUEVO PACIENTE EN BD_HOSPITAL
       ========================================================= -->
  <div id="modalNuevoPaciente" class="modal-backdrop no-print">
    <div class="modal-card" style="max-width: 540px;">
      <div class="modal-header">
        <h2 class="modal-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
          Registrar Nuevo Paciente Institucional
        </h2>
        <button type="button" id="btnCloseModalNuevoPaciente" class="btn-close">&times;</button>
      </div>
      <form id="formNuevoPaciente" class="modal-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
          <div>
            <label class="filter-label">DNI / Documento (*):</label>
            <input type="text" id="npDni" class="filter-control" style="width:100%;" required maxlength="15" placeholder="8 digitos DNI">
          </div>
          <div>
            <label class="filter-label">Historia Clinica:</label>
            <input type="text" id="npHistoria" class="filter-control" style="width:100%;" placeholder="Ej: HC-001050">
          </div>
        </div>
        <div style="margin-bottom:12px;">
          <label class="filter-label">Nombres (*):</label>
          <input type="text" id="npNombres" class="filter-control" style="width:100%;" required placeholder="Ej: Carlos Alberto">
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
          <div>
            <label class="filter-label">Apellido Paterno (*):</label>
            <input type="text" id="npPaterno" class="filter-control" style="width:100%;" required placeholder="Ej: Vasquez">
          </div>
          <div>
            <label class="filter-label">Apellido Materno:</label>
            <input type="text" id="npMaterno" class="filter-control" style="width:100%;" placeholder="Ej: Paredes">
          </div>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:18px;">
          <div>
            <label class="filter-label">Sexo (*):</label>
            <select id="npSexo" class="filter-control" style="width:100%;">
              <option value="M">MASCULINO (M)</option>
              <option value="F">FEMENINO (F)</option>
            </select>
          </div>
          <div>
            <label class="filter-label">Edad (Años):</label>
            <input type="number" id="npEdad" class="filter-control" style="width:100%;" min="0" max="130" placeholder="Ej: 35">
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:8px;">
          <button type="button" id="btnCancelNuevoPaciente" class="btn btn-secondary">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar en BD</button>
        </div>
      </form>
    </div>
  </div>

  <!-- =========================================================
       MODAL: HISTORIAL RAPIDO (MODAL FLOTANTE)
       ========================================================= -->
  <div id="modalHistorial" class="modal-backdrop no-print">
    <div class="modal-card">
      <div class="modal-header">
        <h2 class="modal-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          Historial de Analisis Clinicos Registrados
        </h2>
        <button type="button" id="btnCloseHistorial" class="btn-close">&times;</button>
      </div>

      <div class="modal-body">
        <div class="history-search-bar">
          <input type="text" id="txtBuscarHistorial" class="search-input" placeholder="Buscar por DNI, NÂ° Historia Clinica o Apellidos del paciente...">
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Codigo</th>
              <th>Fecha</th>
              <th>Paciente</th>
              <th>DNI</th>
              <th>Historia</th>
              <th>Servicio</th>
              <th style="text-align: right;">Accion</th>
            </tr>
          </thead>
          <tbody id="tbodyHistorial">
            <!-- Filas dinamicas -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- =========================================================
       MODAL: FICHA DETALLADA DEL ANALISIS
       ========================================================= -->
  <div id="modalVerAnalisis" class="modal-backdrop no-print" style="display:none;">
    <div class="modal-card" style="max-width: 820px; width: 95%;">
      <div class="modal-header" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff;">
        <h2 class="modal-title" style="color: #ffffff; display: flex; align-items: center; gap: 8px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
          Ficha Detallada del Análisis Clínico: <span id="lblModalVerCodigo" style="font-weight: 800;">-</span>
        </h2>
        <button type="button" id="btnCloseModalVerAnalisis" class="btn-close" style="color: #ffffff;">&times;</button>
      </div>
      <div class="modal-body" id="bodyModalVerAnalisis" style="padding: 20px; max-height: 75vh; overflow-y: auto;">
        <div style="text-align:center; padding:30px; color:#94a3b8;">Cargando ficha del análisis...</div>
      </div>
      <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="btn btn-outline" id="btnModalVerCerrar">Cerrar</button>
        <div style="display: flex; gap: 8px;">
          <button type="button" class="btn btn-primary" id="btnModalVerEditar">
            ✏️ Editar Este Análisis
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenedor de Notificaciones Toast -->
  <div id="toastContainer" class="toast-container no-print"></div>

  <!-- Libreria de Graficos Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Script Principal -->
  <script src="assets/js/laboratorio.js"></script>
</body>
</html>
