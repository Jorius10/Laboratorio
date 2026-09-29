/**
 * =========================================================
 * SISTEMA DE LABORATORIO CLINICO - HOSPITAL DISTRITAL PACASMAYO
 * Control Multi-Modulo: Dashboard, Formato, Pacientes, Reportes, Historial y Catalogo
 * =========================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // ==========================================
  // ESTADO GLOBAL DE LA APLICACION (SPA STATE)
  // ==========================================
  const state = {
    currentTab: 'viewDashboard',
    currentFormat: 'bioquimica',
    pacienteActual: null,
    pacientesCatalogo: [],
    solicitudIdEnEdicion: null,
    pacienteEnEdicionID: null,
    parametrosMap: {},
    examenes: [],
    servicios: [],
    medicos: [],
    historialCompleto: [],
    currentReportData: null
  };

  // ==========================================
  // CACHE DE ELEMENTOS DEL DOM
  // ==========================================
  const dom = {
    // Navegacion
    navButtons: document.querySelectorAll('.nav-btn'),
    viewSections: document.querySelectorAll('.view-section'),
    btnGlobalNuevo: document.getElementById('btnGlobalNuevo'),
    btnGlobalImprimir: document.getElementById('btnGlobalImprimir'),
    btnToggleDarkMode: document.getElementById('btnToggleDarkMode'),

    // Dashboard
    statHoy: document.getElementById('statHoy'),
    statPacientes: document.getElementById('statPacientes'),
    statPendientesSem: document.getElementById('statPendientesSem'),
    statAnuladosSem: document.getElementById('statAnuladosSem'),
    statValidadosSem: document.getElementById('statValidadosSem'),
    containerDemandaServicios: document.getElementById('containerDemandaServicios'),
    btnRefreshDashboard: document.getElementById('btnRefreshDashboard'),
    btnVerTodoHistorialDash: document.getElementById('btnVerTodoHistorialDash'),
    btnQuickFormato: document.getElementById('btnQuickFormato'),
    btnQuickPacientes: document.getElementById('btnQuickPacientes'),
    btnQuickReportes: document.getElementById('btnQuickReportes'),

    // Formato Oficial (Campos Paciente)
    cboPaciente: null, // reemplazado por autocomplete
    txtBuscarPaciente: document.getElementById('txtBuscarPaciente'),
    btnLimpiarPaciente: document.getElementById('btnLimpiarPaciente'),
    patientAutocomplete: document.getElementById('patientAutocomplete'),
    txtApellidos: document.getElementById('txtApellidos'),
    txtNombres: document.getElementById('txtNombres'),
    pacienteDni: document.getElementById('txtDni'),
    pacienteEdad: document.getElementById('txtEdad'),
    pacienteHc: document.getElementById('txtHistoriaClinica'),
    radioSexoM: document.getElementById('sexoM'),
    radioSexoF: document.getElementById('sexoF'),
    txtServicio: document.getElementById('txtServicio'),
    listaServicios: document.getElementById('listaServicios'),
    txtMedico: document.getElementById('txtMedico'),
    listaMedicos: document.getElementById('listaMedicos'),
    cboEstadoSolicitud: document.getElementById('cboEstadoSolicitud'),
    txtFechaToma: document.getElementById('txtFechaToma'),
    txtFechaEmision: document.getElementById('txtFechaEmision'),
    txtDiagnostico: document.getElementById('txtDiagnostico'),
    txtOtrosExamenes: document.getElementById('txtOtrosExamenes'),
    txtObservaciones: document.getElementById('txtObservaciones'),
    bannerModoEdicion: document.getElementById('bannerModoEdicion'),
    lblEditCodigo: document.getElementById('lblEditCodigo'),
    btnCancelarEdicion: document.getElementById('btnCancelarEdicion'),

    // Formato Botones
    btnGuardar: document.getElementById('btnGuardar'),
    btnImprimir: document.getElementById('btnImprimir'),
    btnNuevo: document.getElementById('btnNuevo'),
    btnDemo: document.getElementById('btnDemo'),

    // Directorio de Pacientes
    txtDirectorioSearch: document.getElementById('txtDirectorioSearch'),
    containerListaPacientes: document.getElementById('containerListaPacientes'),
    panelFichaPaciente: document.getElementById('panelFichaPaciente'),
    lblTotalPacientesEncontrados: document.getElementById('lblTotalPacientesEncontrados'),
    btnAbrirModalNuevoPaciente: document.getElementById('btnAbrirModalNuevoPaciente'),
    modalNuevoPaciente: document.getElementById('modalNuevoPaciente'),
    btnCloseModalNuevoPaciente: document.getElementById('btnCloseModalNuevoPaciente'),
    btnCancelNuevoPaciente: document.getElementById('btnCancelNuevoPaciente'),
    formNuevoPaciente: document.getElementById('formNuevoPaciente'),

    // Reportes
    repFechaInicio: document.getElementById('repFechaInicio'),
    repFechaFin: document.getElementById('repFechaFin'),
    repServicio: document.getElementById('repServicio'),
    repEstado: document.getElementById('repEstado'),
    btnFiltrarReporte: document.getElementById('btnFiltrarReporte'),
    btnExportarReporteCSV: document.getElementById('btnExportarReporteCSV'),
    btnImprimirReporte: document.getElementById('btnImprimirReporte'),
    repTotalAnalisis: document.getElementById('repTotalAnalisis'),
    repValidados: document.getElementById('repValidados'),
    repPendientes: document.getElementById('repPendientes'),
    repAnulados: document.getElementById('repAnulados'),
    tbodyReporte: document.getElementById('tbodyReporte'),

    // Catalogo
    containerCatalogo: document.getElementById('containerCatalogo'),

    // Modal Flotante de Historial Rapido
    modalHistorial: document.getElementById('modalHistorial'),
    btnCloseHistorial: document.getElementById('btnCloseHistorial'),
    txtBuscarHistorial: document.getElementById('txtBuscarHistorial'),
    tbodyHistorial: document.getElementById('tbodyHistorial'),

    // Toast Container
    toastContainer: document.getElementById('toastContainer'),
  };

  // Exponer helpers globales para eventos onclick en HTML dinamico
  //window.loadAndOpenReport = loadAndOpenReport;
  window.editarSolicitud = editarSolicitud;
  window.eliminarSolicitud = eliminarSolicitud;
  window.cambiarEstadoSolicitud = cambiarEstadoSolicitud;
  window.cancelarEdicion = cancelarEdicion;
  window.resetForm = resetForm;
  window.setReportDatePreset = setReportDatePreset;

  // Inicializacion
  init();

  async function init() {
    setupDateTimeDefaults();
    setupNavigation();
    setupEventListeners();
    await initLoggedDoctor();
    await loadInitialCatalogs();
    await loadDashboardData();

    // Check url parameter modulo
    const urlParams = new URLSearchParams(window.location.search);
    const modParam = urlParams.get('modulo');
    if (modParam) {
      switchTab('viewFormato');
    }
  }

  async function initLoggedDoctor() {
    let medico = null;
    try {
      const res = await fetch('api/auth_medico.php?action=check');
      const data = await res.json();
      if (data.success && data.authenticated && data.medico) {
        medico = data.medico;
      }
    } catch (e) {
      console.error('Error verificando sesion de medico:', e);
    }

    if (!medico) {
      const local = localStorage.getItem('medico_activo');
      if (local) {
        try { medico = JSON.parse(local); } catch(e) {}
      }
    }

    if (medico) {
      state.medicoLogueado = medico;
      const hdrName = document.getElementById('hdrDoctorName');
      const hdrSpec = document.getElementById('hdrDoctorSpec');
      if (hdrName) hdrName.textContent = `🩺 Dr(a). ${medico.NombreCompleto || medico.NombreSimple}`;
      if (hdrSpec) hdrSpec.textContent = medico.EspecialidadOcupacion || 'Médico';

      // Pre-llenar Medico Solicitante si esta vacio
      if (dom.txtMedico && !dom.txtMedico.value.trim()) {
        dom.txtMedico.value = medico.NombreCompleto || medico.NombreSimple;
      }
    }
  }

  function setupDateTimeDefaults() {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const nowFormatted = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;

    if (dom.txtFechaToma) dom.txtFechaToma.value = nowFormatted;
    if (dom.txtFechaEmision) dom.txtFechaEmision.value = nowFormatted;

    // Fechas iniciales para reportes: primer dia del mes actual hasta hoy
    const primerDiaMes = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-01`;
    const hoyDate = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    if (dom.repFechaInicio) dom.repFechaInicio.value = primerDiaMes;
    if (dom.repFechaFin) dom.repFechaFin.value = hoyDate;
  }

  // ==========================================
  // NAVEGACION ENTRE MODULOS (TABS)
  // ==========================================
  function setupNavigation() {
    const navDropdown = document.getElementById('navDropdownFormato');
    const navBtnFormato = document.getElementById('navBtnFormato');
    const navDropdownItems = document.querySelectorAll('.nav-dropdown-item[data-formato]');
    const formatoTabs = document.querySelectorAll('.formato-tab[data-formato]');
    const formatoPanels = document.querySelectorAll('.formato-panel');

    // Toggle dropdown on main button click
    if (navBtnFormato && navDropdown) {
      navBtnFormato.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = navDropdown.classList.contains('open');
        navDropdown.classList.toggle('open', !isOpen);

        if (state.currentTab !== 'viewFormato') {
          switchTab('viewFormato')
        };

        // Si no esta en viewFormato, ir a la vista
        if (state.currentTab !== 'viewFormato') {
          switchTab('viewFormato')
        };
      });
    }

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (navDropdown && !navDropdown.contains(e.target)) {
        navDropdown.classList.remove('open');
      }
    });

    // Cerrar dropdown al presionar Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && navDropdown) {
        navDropdown.classList.remove('open');
      }
    });

    // Clics en items del dropdown de formato
    navDropdownItems.forEach(item => {
      item.addEventListener('click', (e) => {
        e.stopPropagation();
        const fmt = item.dataset.formato;
        switchFormat(fmt);
        if (state.currentTab !== 'viewFormato') {
          switchTab('viewFormato');
        }
        if (navDropdown) navDropdown.classList.remove('open');
      });
    });

    // Clics en pestañas internas del formato
    formatoTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        switchFormat(tab.dataset.formato);
      });
    });

    function switchFormat(fmt) {
      state.currentFormat = fmt;
      // Actualizar items del dropdown
      navDropdownItems.forEach(i => i.classList.toggle('active', i.dataset.formato === fmt));
      // Actualizar barra de tabs
      formatoTabs.forEach(t => t.classList.toggle('active', t.dataset.formato === fmt));
      // Actualizar paneles visibles
      formatoPanels.forEach(p => {
        if (p.id === 'panel' + fmt.charAt(0).toUpperCase() + fmt.slice(1)) {
          p.classList.add('active');
        } else {
          p.classList.remove('active');
        }
      });
    }

    window.switchFormat = switchFormat;

    // Botones regulares de navegacion
    dom.navButtons.forEach(btn => {
      if (btn === navBtnFormato) return;
      btn.addEventListener('click', () => {
        const targetId = btn.dataset.target;
        if (targetId) {
          switchTab(targetId);
        }
      });
    });

    if (dom.btnGlobalNuevo) {
      dom.btnGlobalNuevo.addEventListener('click', () => {
        cancelarEdicion();
        switchTab('viewFormato');
        resetForm();
      });
    }

    if (dom.btnGlobalImprimir) {
      dom.btnGlobalImprimir.addEventListener('click', () => {
        if (state.currentTab === 'viewReportes') {
          printReport();
        } else {
          switchTab('viewFormato');
          setTimeout(() => window.print(), 150);
        }
      });
    }

    // Modo Oscuro
    function applyDarkMode(isDark) {
      document.body.classList.toggle('dark-mode', isDark);
      const iconDark  = document.getElementById('iconDark');
      const iconLight = document.getElementById('iconLight');
      const label     = document.getElementById('labelDarkMode');
      if (iconDark)  iconDark.style.display  = isDark ? 'none'  : '';
      if (iconLight) iconLight.style.display = isDark ? ''      : 'none';
      if (label)     label.textContent        = isDark ? 'Modo Claro' : 'Modo Oscuro';
      localStorage.setItem('darkMode', isDark ? '1' : '0');
    }

    // Restaurar preferencia guardada
    if (localStorage.getItem('darkMode') === '1') {
      applyDarkMode(true);
    }

    if (dom.btnToggleDarkMode) {
      dom.btnToggleDarkMode.addEventListener('click', () => {
        applyDarkMode(!document.body.classList.contains('dark-mode'));
      });
    }

    if (dom.btnQuickFormato) {
      dom.btnQuickFormato.addEventListener('click', () => {
        switchTab('viewFormato');
      });
    }

    if (dom.btnQuickPacientes) {
      dom.btnQuickPacientes.addEventListener('click', () => {
        switchTab('viewPacientes');
      });
    }

    if (dom.btnQuickReportes) {
      dom.btnQuickReportes.addEventListener('click', () => {
        switchTab('viewReportes');
      });
    }

    if (dom.btnVerTodoHistorialDash) {
      dom.btnVerTodoHistorialDash.addEventListener('click', () => {
        switchTab('viewReportes');
      });
    }
  }

  function switchTab(targetId) {
    state.currentTab = targetId;

    // Cerrar siempre el dropdown al navegar
    const navDropdown = document.getElementById('navDropdownFormato');
    if (navDropdown) {
      navDropdown.classList.remove('open');
    }

    // Actualizar botones del menu
    dom.navButtons.forEach(btn => {
      if (btn.dataset.target === targetId) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    // Actualizar secciones de vista
    dom.viewSections.forEach(section => {
      if (section.id === targetId) {
        section.classList.add('active');
      } else {
        section.classList.remove('active');
      }
    });

    // Acciones al ingresar a cada pestaña
    if (targetId === 'viewDashboard') {
      loadDashboardData();
    } else if (targetId === 'viewPacientes') {
      loadPatientsDirectory();
    } else if (targetId === 'viewReportes') {
      generateReport();
    } else if (targetId === 'viewCatalogo') {
      renderCatalog();
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  window.switchTab = switchTab;
  // ==========================================
  // CARGA DE CATALOGOS INICIALES (SQL SERVER)
  // ==========================================
  async function loadInitialCatalogs() {
    try {
      // 1. Parametros y Examenes
      const resParams = await fetch('api/parametros.php');
      const dataParams = await resParams.json();
      if (dataParams.success) {
        state.parametrosMap = dataParams.parametros_map;
        state.examenes = dataParams.examenes;
        renderCatalog();
      }

      // 2. Servicios Solicitantes (Datalist y Selects)
      const resServ = await fetch('api/servicios.php');
      const dataServ = await resServ.json();
      if (dataServ.success && dataServ.data) {
        state.servicios = dataServ.data;

        if (dom.listaServicios) {
          dom.listaServicios.innerHTML = '';
          dataServ.data.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.Nombre;
            opt.setAttribute('data-id', s.ServicioID);
            dom.listaServicios.appendChild(opt);
          });
        }

        if (dom.repServicio) {
          dom.repServicio.innerHTML = '<option value="0">Todos los Servicios</option>';
          dataServ.data.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.ServicioID;
            opt.textContent = s.Nombre;
            dom.repServicio.appendChild(opt);
          });
        }
      }

      // 3. Medicos / Personal Solicitante (Datalist)
      const resMed = await fetch('api/medicos.php');
      const dataMed = await resMed.json();
      if (dataMed.success && dataMed.data) {
        state.medicos = dataMed.data;
        if (dom.listaMedicos) {
          dom.listaMedicos.innerHTML = '';
          dataMed.data.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.NombreCompleto;
            opt.setAttribute('data-id', m.PersonalID);
            dom.listaMedicos.appendChild(opt);
          });
        }
      }

      // 4. Catalogo de Pacientes para Autocomplete
      await loadPatientsForAutocomplete();

    } catch (err) {
      console.error('Error al inicializar catalogos:', err);
      showToast('Error cargando catalogos institucionales', 'error');
    }
  }

  async function loadPatientsForAutocomplete() {
    try {
      const res = await fetch('api/pacientes.php?limit=500');
      const data = await res.json();
      if (data.success && data.data) {
        state.pacientesCatalogo = data.data;
      }
    } catch (e) {
      console.warn('Error cargando catalogo de pacientes:', e);
    }
  }

  function setupPatientAutocomplete() {
    const input = dom.txtBuscarPaciente;
    const dropdown = dom.patientAutocomplete;
    const btnClear = dom.btnLimpiarPaciente;
    if (!input || !dropdown) return;

    let selectedIndex = -1;
    let searchTimeout = null;

    function hideDropdown() {
      dropdown.style.display = 'none';
      dropdown.innerHTML = '';
      selectedIndex = -1;
    }

    function renderSuggestions(list, query) {
      dropdown.innerHTML = '';
      if (!list || list.length === 0) {
        dropdown.innerHTML = `
          <div style="padding: 16px; font-size: 12.5px; color: #64748b; text-align: center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="margin: 0 auto 6px; display: block;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            No se encontraron pacientes coincidentes
          </div>`;
        dropdown.style.display = 'block';
        return;
      }

      // Header informativo del dropdown
      const header = document.createElement('div');
      header.className = 'ac-header';
      header.innerHTML = `<span>Pacientes Encontrados</span><span>${list.length} registros</span>`;
      dropdown.appendChild(header);

      list.forEach((p, index) => {
        const item = document.createElement('div');
        item.className = 'ac-patient-item';
        item.dataset.index = index;

        const pName = p.NombreCompleto || trim((p.ApellidoPaterno || '') + ' ' + (p.ApellidoMaterno || '') + ', ' + (p.Nombres || ''));
        const dni = p.NumeroDocumento || '';
        const hc = p.HistoriaClinica || '';
        const edad = p.Edad !== null && p.Edad !== undefined ? `${p.Edad} a.` : '';
        const isFemale = p.SexoCodigo === 'F';

        // Calcular iniciales para el avatar
        const initPaterno = (p.ApellidoPaterno || pName || 'P').charAt(0).toUpperCase();
        const initNombres = (p.Nombres || '').charAt(0).toUpperCase();
        const initials = `${initPaterno}${initNombres || ''}`;

        let nameHTML = pName;
        if (query) {
          const esc = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          const re = new RegExp(`(${esc})`, 'gi');
          nameHTML = pName.replace(re, '<strong>$1</strong>');
        }

        let metaHTML = '';
        if (dni) metaHTML += `<span class="ac-badge ac-badge-dni">🪪 DNI: ${dni}</span>`;
        if (hc) metaHTML += `<span class="ac-badge ac-badge-hc">📋 HC: ${hc}</span>`;
        if (edad) metaHTML += `<span class="ac-badge ac-badge-age">🎂 ${edad}</span>`;

        item.innerHTML = `
          <div class="ac-avatar ${isFemale ? 'female' : ''}">${initials}</div>
          <div class="ac-details">
            <div class="ac-name">${nameHTML}</div>
            <div class="ac-meta">${metaHTML || '<span style="font-size:11px; color:#94a3b8;">Sin documento</span>'}</div>
          </div>
          <div class="ac-select-btn">
            <span>Seleccionar</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </div>
        `;

        item.addEventListener('mousedown', (e) => {
          e.preventDefault();
          selectPatient(p);
          hideDropdown();
          if (btnClear) btnClear.style.display = 'inline-flex';
        });

        dropdown.appendChild(item);
      });

      dropdown.style.display = 'block';
    }

    async function fetchAndRender(query) {
      const q = query.trim().toLowerCase();
      if (!q) {
        hideDropdown();
        return;
      }

      // 1. Filtrar localmente si catalogos ya cargados
      let matches = (state.pacientesCatalogo || []).filter(p => {
        const full = (p.NombreCompleto || `${p.ApellidoPaterno} ${p.ApellidoMaterno} ${p.Nombres}`).toLowerCase();
        const dni = (p.NumeroDocumento || '').toLowerCase();
        const hc = (p.HistoriaClinica || '').toLowerCase();
        return full.includes(q) || dni.includes(q) || hc.includes(q);
      });

      // 2. Si hay pocos o ningun resultado local, consultar backend
      if (matches.length < 5) {
        try {
          const res = await fetch(`api/pacientes.php?q=${encodeURIComponent(q)}`);
          const data = await res.json();
          if (data.success && data.data && data.data.length > 0) {
            const map = new Map();
            matches.forEach(m => map.set(m.PacienteID, m));
            data.data.forEach(m => map.set(m.PacienteID, m));
            matches = Array.from(map.values());
          }
        } catch (e) {
          console.warn('Error buscando pacientes backend:', e);
        }
      }

      renderSuggestions(matches.slice(0, 12), q);
    }

    input.addEventListener('input', (e) => {
      const val = e.target.value;
      if (btnClear) btnClear.style.display = val ? 'inline-flex' : 'none';

      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        fetchAndRender(val);
      }, 120);
    });

    input.addEventListener('focus', () => {
      if (input.value.trim().length >= 1) {
        fetchAndRender(input.value);
      }
    });

    input.addEventListener('keydown', (e) => {
      const items = dropdown.querySelectorAll('.ac-patient-item');
      if (!items.length || dropdown.style.display === 'none') return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = (selectedIndex + 1) % items.length;
        updateActiveItem(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
        updateActiveItem(items);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (selectedIndex >= 0 && items[selectedIndex]) {
          items[selectedIndex].dispatchEvent(new MouseEvent('mousedown'));
        }
      } else if (e.key === 'Escape') {
        hideDropdown();
      }
    });

    function updateActiveItem(items) {
      items.forEach((it, idx) => {
        if (idx === selectedIndex) {
          it.classList.add('active');
          it.scrollIntoView({ block: 'nearest' });
        } else {
          it.classList.remove('active');
        }
      });
    }

    if (btnClear) {
      btnClear.addEventListener('click', () => {
        input.value = '';
        btnClear.style.display = 'none';
        hideDropdown();
        resetForm();
      });
    }

    document.addEventListener('click', (e) => {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        hideDropdown();
      }
    });
  }

  // ==========================================
  // BINDING DE EVENTOS DEL SISTEMA
  // ==========================================
  function setupEventListeners() {
    // Autocomplete de busqueda de pacientes
    setupPatientAutocomplete();

    // Boton cancelar edicion
    if (dom.btnCancelarEdicion) {
      dom.btnCancelarEdicion.addEventListener('click', cancelarEdicion);
    }

    // Inputs de resultados en tiempo real para calculo de rangos
    document.querySelectorAll('.res-input').forEach(input => {
      input.addEventListener('input', () => evaluateResultRange(input));
    });

    if (dom.radioSexoM) dom.radioSexoM.addEventListener('change', reevaluateAllRanges);
    if (dom.radioSexoF) dom.radioSexoF.addEventListener('change', reevaluateAllRanges);

    // Botones del Formato
    if (dom.btnGuardar) dom.btnGuardar.addEventListener('click', saveLabReport);
    if (dom.btnImprimir) dom.btnImprimir.addEventListener('click', printLabReport);
    if (dom.btnNuevo) dom.btnNuevo.addEventListener('click', () => {
      cancelarEdicion();
      resetForm();
    });
    if (dom.btnDemo) dom.btnDemo.addEventListener('click', loadDemoData);

    // Dashboard
    if (dom.btnRefreshDashboard) {
      dom.btnRefreshDashboard.addEventListener('click', () => {
        loadDashboardData();
        showToast('Datos del dashboard actualizados', 'info');
      });
    }

    // Directorio de Pacientes
    let dirSearchTimeout = null;
    if (dom.txtDirectorioSearch) {
      dom.txtDirectorioSearch.addEventListener('input', (e) => {
        clearTimeout(dirSearchTimeout);
        dirSearchTimeout = setTimeout(() => {
          loadPatientsDirectory(e.target.value.trim());
        }, 300);
      });
    }

    // Modal Ver Ficha de Analisis
    const btnCloseModalVer = document.getElementById('btnCloseModalVerAnalisis');
    const btnModalVerCerrar = document.getElementById('btnModalVerCerrar');
    if (btnCloseModalVer) btnCloseModalVer.addEventListener('click', cerrarModalVerAnalisis);
    if (btnModalVerCerrar) btnModalVerCerrar.addEventListener('click', cerrarModalVerAnalisis);

    // Modal Nuevo / Editar Paciente
    if (dom.btnAbrirModalNuevoPaciente) {
      dom.btnAbrirModalNuevoPaciente.addEventListener('click', () => {
        abrirModalEditarPaciente(null);
      });
    }

    const closeModalNuevoPaciente = () => {
      const modal = dom.modalNuevoPaciente || document.getElementById('modalNuevoPaciente');
      if (modal) {
        modal.classList.remove('open');
        modal.style.display = 'none';
      }
      if (dom.formNuevoPaciente) dom.formNuevoPaciente.reset();
      state.pacienteEnEdicionID = null;
    };

    if (dom.btnCloseModalNuevoPaciente) dom.btnCloseModalNuevoPaciente.addEventListener('click', closeModalNuevoPaciente);
    if (dom.btnCancelNuevoPaciente) dom.btnCancelNuevoPaciente.addEventListener('click', closeModalNuevoPaciente);

    if (dom.formNuevoPaciente) {
      dom.formNuevoPaciente.addEventListener('submit', handleCreatePatientSubmit);
    }

    // Reportes
    if (dom.btnFiltrarReporte) dom.btnFiltrarReporte.addEventListener('click', generateReport);
    if (dom.btnExportarReporteCSV) dom.btnExportarReporteCSV.addEventListener('click', exportReportCSV);
    if (dom.btnImprimirReporte) dom.btnImprimirReporte.addEventListener('click', printReport);

    // Modal Historial Rapido
    if (dom.btnCloseHistorial) dom.btnCloseHistorial.addEventListener('click', closeHistoryModal);
    if (dom.txtBuscarHistorial) {
      dom.txtBuscarHistorial.addEventListener('input', (e) => {
        loadHistoryList(e.target.value.trim());
      });
    }
  }

  // ==========================================
  // MODULO 1: 📊 DASHBOARD
  // ==========================================
  async function loadDashboardData() {
    try {
      const res = await fetch('api/dashboard.php');
      const data = await res.json();

      if (!data.success) {
        showToast('Error cargando metricas del dashboard', 'error');
        return;
      }

      // 1. Contadores
      const c = data.counters || {};
      if (dom.statHoy)      dom.statHoy.textContent      = c.AnalisisHoy    ?? 0;
      if (dom.statPacientes) dom.statPacientes.textContent = c.TotalPacientes ?? 0;

      // 2. Panel Semaforo de Estados
      const pendientes = parseInt(c.Pendientes ?? 0);
      const anulados   = parseInt(c.Anulados   ?? 0);
      const validados  = parseInt(c.Validados  ?? 0);

      if (dom.statPendientesSem) dom.statPendientesSem.textContent = pendientes;
      if (dom.statAnuladosSem)   dom.statAnuladosSem.textContent   = anulados;
      if (dom.statValidadosSem)  dom.statValidadosSem.textContent  = validados;

      // 3. Demanda por Servicio (Barras)
      if (dom.containerDemandaServicios) {
        dom.containerDemandaServicios.innerHTML = '';
        const servs = data.servicios_demanda || [];

        if (servs.length === 0) {
          dom.containerDemandaServicios.innerHTML = '<p style="color:#94a3b8; font-size:13px; text-align:center; padding:10px;">Sin solicitudes registradas aun.</p>';
        } else {
          const maxTotal = Math.max(...servs.map(s => parseInt(s.Total) || 1));
          servs.forEach(s => {
            const pct = Math.round((parseInt(s.Total) / maxTotal) * 100);
            const item = document.createElement('div');
            item.className = 'service-bar-item';
            item.innerHTML = `
              <div class="service-bar-header">
                <span>${escapeHtml(s.ServicioNombre || 'Servicio General')}</span>
                <span><strong>${s.Total}</strong> analisis</span>
              </div>
              <div class="service-bar-track">
                <div class="service-bar-fill" style="width: ${pct}%;"></div>
              </div>
            `;
            dom.containerDemandaServicios.appendChild(item);
          });
        }
      }

      // 4. Renderizar Graficos de Chart.js
      renderDashboardCharts(data);

    } catch (err) {
      console.error('Error cargando dashboard:', err);
    }
  }

  // Variables para instancias de graficos
  let chartTendenciaInstance = null;
  let chartEstadosInstance = null;
  let chartServiciosInstance = null;

  function renderDashboardCharts(data) {
    if (typeof Chart === 'undefined') {
      console.warn('Chart.js no esta disponible');
      return;
    }

    Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';

    // 1. GRAFICO TENDENCIA (ULTIMOS 7 DIAS)
    const ctxTendencia = document.getElementById('chartTendencia');
    if (ctxTendencia) {
      let labels = [];
      let counts = [];

      if (data.tendencia_dias && data.tendencia_dias.length > 0) {
        labels = data.tendencia_dias.map(t => {
          const parts = t.Fecha.split('-');
          return parts.length >= 3 ? `${parts[2]}/${parts[1]}` : t.Fecha;
        });
        counts = data.tendencia_dias.map(t => parseInt(t.Total) || 0);
      } else {
        const now = new Date();
        for (let i = 6; i >= 0; i--) {
          const d = new Date();
          d.setDate(now.getDate() - i);
          const pad = n => String(n).padStart(2, '0');
          labels.push(`${pad(d.getDate())}/${pad(d.getMonth() + 1)}`);
          counts.push(i === 0 ? (parseInt(data.counters?.AnalisisHoy) || 0) : 0);
        }
      }

      if (chartTendenciaInstance) chartTendenciaInstance.destroy();

      chartTendenciaInstance = new Chart(ctxTendencia, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Solicitudes',
            data: counts,
            borderColor: '#0284c7',
            backgroundColor: 'rgba(2, 132, 199, 0.10)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#0284c7',
            pointRadius: 4,
            pointHoverRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0f172a',
              titleFont: { size: 12, weight: 'bold' },
              bodyFont: { size: 12 },
              padding: 10,
              cornerRadius: 6,
              callbacks: {
                label: (ctx) => ` ${ctx.parsed.y} solicitudes de analisis`
              }
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: '#64748b', font: { size: 11 } }
            },
            y: {
              beginAtZero: true,
              ticks: { precision: 0, color: '#64748b', font: { size: 11 } },
              grid: { color: '#f1f5f9' }
            }
          }
        }
      });
    }

    // 2. GRAFICO DISTRIBUCION POR ESTADO (DOUGHNUT) - Solo 3 estados
    const ctxEstados = document.getElementById('chartEstados');
    if (ctxEstados) {
      const pendientes = parseInt(data.counters?.Pendientes || 0);
      const anulados   = parseInt(data.counters?.Anulados   || 0);
      const validados  = parseInt(data.counters?.Validados  || 0);

      const total3 = pendientes + anulados + validados;
      const estadoLabels = ['Pendiente', 'Anulado', 'Validado'];
      const estadoData   = total3 > 0 ? [pendientes, anulados, validados] : [1, 1, 1];
      // Amarillo, Rojo, Verde
      const estadoColors = total3 > 0
        ? ['#f59e0b', '#ef4444', '#10b981']
        : ['#fde68a', '#fca5a5', '#6ee7b7'];

      if (chartEstadosInstance) chartEstadosInstance.destroy();

      chartEstadosInstance = new Chart(ctxEstados, {
        type: 'doughnut',
        data: {
          labels: estadoLabels,
          datasets: [{
            data: estadoData,
            backgroundColor: estadoColors,
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '68%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                boxWidth: 14,
                font: { size: 12, weight: '600' },
                color: '#475569',
                padding: 16
              }
            },
            tooltip: {
              backgroundColor: '#0f172a',
              callbacks: {
                label: (ctx) => {
                  if (total3 === 0) return ' Sin solicitudes registradas';
                  const pct = Math.round((ctx.raw / total3) * 100);
                  return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                }
              }
            }
          }
        }
      });
    }

    // 3. GRAFICO DEMANDA POR SERVICIOS (HORIZONTAL BAR)
    const ctxServicios = document.getElementById('chartServicios');
    if (ctxServicios) {
      const servs = data.servicios_demanda || [];
      const sLabels = servs.map(s => s.ServicioNombre || 'General');
      const sTotals = servs.map(s => parseInt(s.Total) || 0);

      if (chartServiciosInstance) chartServiciosInstance.destroy();

      if (sLabels.length > 0) {
        chartServiciosInstance = new Chart(ctxServicios, {
          type: 'bar',
          data: {
            labels: sLabels,
            datasets: [{
              label: 'Total Analisis',
              data: sTotals,
              backgroundColor: '#38bdf8',
              borderRadius: 4,
              borderSkipped: false
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: '#0f172a',
                callbacks: {
                  label: (ctx) => ` ${ctx.parsed.x} analisis`
                }
              }
            },
            scales: {
              x: {
                beginAtZero: true,
                ticks: { precision: 0, color: '#64748b', font: { size: 10 } },
                grid: { color: '#f1f5f9' }
              },
              y: {
                grid: { display: false },
                ticks: { color: '#334155', font: { size: 11, weight: '600' } }
              }
            }
          }
        });
      }
    }
  }

  // ==========================================
  // MODULO 3: 👥 BUSCAR Y DIRECTORIO DE PACIENTES
  // ==========================================
  async function loadPatientsDirectory(query = '') {
    if (!dom.containerListaPacientes) return;

    dom.containerListaPacientes.innerHTML = '<p style="color:#94a3b8; padding:20px; text-align:center;">Buscando pacientes en BD_HOSPITAL...</p>';

    try {
      const url = `api/pacientes.php?q=${encodeURIComponent(query)}`;
      const res = await fetch(url);
      const data = await res.json();

      if (!data.success || !data.data) {
        dom.containerListaPacientes.innerHTML = '<p style="color:#dc2626; padding:20px;">Error al consultar la base de datos.</p>';
        return;
      }

      const patients = data.data;
      if (dom.lblTotalPacientesEncontrados) {
        dom.lblTotalPacientesEncontrados.textContent = `${patients.length} paciente(s)`;
      }

      if (patients.length === 0) {
        dom.containerListaPacientes.innerHTML = `
          <div style="text-align:center; padding:35px 20px; color:#64748b;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 10px; color:#cbd5e1;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <p style="font-weight:600; color:#334155;">No se encontro ningun paciente con ese criterio.</p>
            <p style="font-size:12px; margin-top:4px;">Puede registrarlo usando el boton superior "+ Registrar Paciente".</p>
          </div>
        `;
        return;
      }

      dom.containerListaPacientes.innerHTML = '';
      patients.forEach(p => {
        const item = document.createElement('div');
        item.className = 'patient-dir-item';

        const pName = p.NombreCompleto || trim((p.ApellidoPaterno || '') + ' ' + (p.ApellidoMaterno || '') + ', ' + (p.Nombres || ''));
        const dni = p.NumeroDocumento || '-';
        const hc = p.HistoriaClinica || '-';
        const sexoStr = p.SexoCodigo === 'F' ? 'Femenino' : (p.SexoCodigo === 'M' ? 'Masculino' : '-');
        const edadStr = p.Edad !== null && p.Edad !== undefined ? `${p.Edad} a.` : '-';
        const isFemale = p.SexoCodigo === 'F';

        const initPaterno = (p.ApellidoPaterno || pName || 'P').charAt(0).toUpperCase();
        const initNombres = (p.Nombres || '').charAt(0).toUpperCase();
        const initials = `${initPaterno}${initNombres || ''}`;

        item.innerHTML = `
          <div class="pdir-avatar ${isFemale ? 'female' : ''}">${initials}</div>
          <div class="pdir-content">
            <div class="pdir-name">${escapeHtml(pName)}</div>
            <div class="pdir-badges">
              <span class="pdir-badge pdir-badge-dni">🪪 DNI: ${escapeHtml(dni)}</span>
              <span class="pdir-badge pdir-badge-hc">📋 HC: ${escapeHtml(hc)}</span>
              <span class="pdir-badge pdir-badge-sex">👤 ${sexoStr}</span>
              <span class="pdir-badge pdir-badge-age">🎂 ${edadStr}</span>
            </div>
          </div>
          <div class="pdir-chevron">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </div>
        `;

        item.addEventListener('click', () => {
          document.querySelectorAll('.patient-dir-item').forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          showPatientDossier(p);
        });
        dom.containerListaPacientes.appendChild(item);
      });

      // Seleccionar el primer paciente por defecto
      if (patients.length > 0) {
        dom.containerListaPacientes.firstChild.classList.add('active');
        showPatientDossier(patients[0]);
      }

    } catch (err) {
      console.error('Error cargando directorio:', err);
      dom.containerListaPacientes.innerHTML = '<p style="color:#dc2626; padding:20px;">Error de conexion con el servidor.</p>';
    }
  }

  async function showPatientDossier(p) {
    if (!dom.panelFichaPaciente) return;

    const sexoStr = p.SexoCodigo === 'M' ? 'Masculino' : (p.SexoCodigo === 'F' ? 'Femenino' : 'No especificado');
    const iniciales = (p.ApellidoPaterno ? p.ApellidoPaterno.charAt(0) : '') + (p.Nombres ? p.Nombres.charAt(0) : '');

    dom.panelFichaPaciente.innerHTML = `
      <div class="dossier-header">
        <div class="dossier-avatar">${iniciales}</div>
        <div style="flex-grow:1;">
          <h3 class="dossier-name">${escapeHtml(p.NombreCompleto)}</h3>
          <p class="dossier-meta">DNI: <strong>${p.NumeroDocumento || '-'}</strong> &bull; N° Historia Clinica: <strong>${p.HistoriaClinica || '-'}</strong></p>
        </div>
      </div>

      <div class="dossier-body">
        <div class="dossier-field">
          <div class="dossier-label">Apellidos</div>
          <div class="dossier-value">${escapeHtml(p.ApellidoPaterno + ' ' + (p.ApellidoMaterno || ''))}</div>
        </div>
        <div class="dossier-field">
          <div class="dossier-label">Nombres</div>
          <div class="dossier-value">${escapeHtml(p.Nombres || '-')}</div>
        </div>
        <div class="dossier-field">
          <div class="dossier-label">Sexo Biologico</div>
          <div class="dossier-value">${sexoStr} (${p.SexoCodigo || '-'})</div>
        </div>
        <div class="dossier-field">
          <div class="dossier-label">Edad Registrada</div>
          <div class="dossier-value">${p.Edad !== null ? p.Edad + ' años' : 'No registrada'}</div>
        </div>
        <div class="dossier-field">
          <div class="dossier-label">Fecha de Nacimiento</div>
          <div class="dossier-value">${p.FechaNacimiento ? p.FechaNacimiento.substring(0, 10) : 'No registrada'}</div>
        </div>
        <div class="dossier-field">
          <div class="dossier-label">Estado en SQL Server</div>
          <div class="dossier-value"><span class="badge badge-validado">ACTIVO</span></div>
        </div>
      </div>

      <div class="dossier-actions" style="display:flex; gap:10px;">
        <button type="button" class="btn btn-secondary" id="btnEditarPacienteDesdeFicha" style="height:42px; padding:0 16px;">
          ✏️ Editar Datos de Paciente
        </button>
        <button type="button" class="btn btn-primary" id="btnIniciarAnalisisDesdeFicha" style="flex-grow:1; justify-content:center; height:42px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
          + Iniciar Nuevo Analisis para este Paciente
        </button>
      </div>

      <div style="font-weight:700; color:#0f172a; margin:24px 0 12px 0; font-size:14px; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        Historial de Analisis Clinicos en este Hospital
      </div>

      <div id="containerHistorialPaciente" style="overflow-x:auto;">
        <p style="color:#94a3b8; font-size:12px; padding:12px 0;">Consultando historial clinico...</p>
      </div>
    `;

    // Asignar boton de editar paciente desde la ficha
    document.getElementById('btnEditarPacienteDesdeFicha').addEventListener('click', () => {
      abrirModalEditarPaciente(p);
    });

    // Asignar boton de crear analisis
    document.getElementById('btnIniciarAnalisisDesdeFicha').addEventListener('click', () => {
      cancelarEdicion();
      selectPatient(p);
      switchTab('viewFormato');
    });

    // Cargar historial de analisis del paciente
    try {
      const res = await fetch(`api/pacientes.php?historial_paciente_id=${p.PacienteID}`);
      const data = await res.json();
      const contHist = document.getElementById('containerHistorialPaciente');

      if (!data.success || !data.data || data.data.length === 0) {
        contHist.innerHTML = '<p style="color:#64748b; font-size:13px; padding:10px 0; background:#f8fafc; border-radius:6px; text-align:center;">Este paciente aun no registra analisis en la base de datos.</p>';
        return;
      }

      let html = `
        <table class="modern-table">
          <thead>
            <tr>
              <th>Codigo</th>
              <th>Fecha</th>
              <th>Servicio</th>
              <th>Medico</th>
              <th>Estado</th>
              <th style="text-align:right;">Accion</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.data.forEach(h => {
        const fStr = h.FechaSolicitud ? h.FechaSolicitud.substring(0, 10) : '-';
        const docName = (h.MedicoPaterno ? h.MedicoPaterno + ', ' : '') + (h.MedicoNombres || '');
        html += `
          <tr>
            <td><strong>${escapeHtml(h.CodigoSolicitud || '#' + h.SolicitudID)}</strong></td>
            <td>${fStr}</td>
            <td>${escapeHtml(h.ServicioNombre || 'LABORATORIO')}</td>
            <td>${escapeHtml(docName || '-')}</td>
            <td>${getStatusBadge(h.Estado)}</td>
            <td style="text-align:right;">
              <div class="table-actions-cell">
                <button type="button" class="btn-action-edit" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;" onclick="verSolicitud(${h.SolicitudID})">
                  👁️ Ver
                </button>
                <button type="button" class="btn-action-edit" onclick="editarSolicitud(${h.SolicitudID})">
                  ✏️ Editar
                </button>
                <button type="button" class="btn-action-delete" onclick="eliminarSolicitud(${h.SolicitudID}, '${h.CodigoSolicitud || '#' + h.SolicitudID}')">
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        `;
      });

      html += '</tbody></table>';
      contHist.innerHTML = html;

    } catch (e) {
      console.warn('Error cargando historial paciente:', e);
    }
  }

  function abrirModalEditarPaciente(p) {
    const modal = dom.modalNuevoPaciente || document.getElementById('modalNuevoPaciente');
    if (!modal) return;
    state.pacienteEnEdicionID = p ? p.PacienteID : null;

    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
      modalTitle.innerHTML = p
        ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Editar Datos del Paciente`
        : `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg> Registrar Nuevo Paciente Institucional`;
    }

    const inputDni = document.getElementById('npDni');
    const inputHc = document.getElementById('npHistoria') || document.getElementById('npHc');
    const inputNombres = document.getElementById('npNombres');
    const inputPaterno = document.getElementById('npPaterno');
    const inputMaterno = document.getElementById('npMaterno');
    const selectSexo = document.getElementById('npSexo');
    const inputEdad = document.getElementById('npEdad');

    if (p) {
      if (inputDni) inputDni.value = p.NumeroDocumento || '';
      if (inputHc) inputHc.value = p.HistoriaClinica || '';
      if (inputNombres) inputNombres.value = p.Nombres || '';
      if (inputPaterno) inputPaterno.value = p.ApellidoPaterno || '';
      if (inputMaterno) inputMaterno.value = p.ApellidoMaterno || '';
      if (selectSexo) selectSexo.value = p.SexoCodigo === 'F' ? 'F' : 'M';
      if (inputEdad) inputEdad.value = p.Edad !== null && p.Edad !== undefined ? p.Edad : '';
    } else {
      if (dom.formNuevoPaciente) dom.formNuevoPaciente.reset();
    }

    modal.classList.add('open');
    modal.style.display = 'flex';
    if (inputDni) inputDni.focus();
  }

  async function handleCreatePatientSubmit(e) {
    e.preventDefault();
    const dni = document.getElementById('npDni').value.trim();
    const hcInput = document.getElementById('npHistoria') || document.getElementById('npHc');
    const hc = hcInput ? hcInput.value.trim() : '';
    const nombres = document.getElementById('npNombres').value.trim();
    const paterno = document.getElementById('npPaterno').value.trim();
    const materno = document.getElementById('npMaterno').value.trim();
    const sexo = document.getElementById('npSexo').value;
    const edadInput = document.getElementById('npEdad');
    const edad = edadInput && edadInput.value !== '' ? parseInt(edadInput.value) : null;

    if (!dni || !nombres || !paterno) {
      showToast('Por favor complete DNI, Nombres y Apellido Paterno', 'error');
      return;
    }

    const isEditing = state.pacienteEnEdicionID !== null;

    try {
      const res = await fetch('api/pacientes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          NumeroDocumento: dni,
          HistoriaClinica: hc,
          Nombres: nombres,
          ApellidoPaterno: paterno,
          ApellidoMaterno: materno,
          SexoCodigo: sexo,
          Edad: edad
        })
      });

      const result = await res.json();
      if (result.success) {
        showToast(isEditing ? `¡Datos de ${nombres} actualizados exitosamente en BD_HOSPITAL!` : '¡Paciente registrado exitosamente en BD_HOSPITAL!', 'success');
        if (dom.modalNuevoPaciente) dom.modalNuevoPaciente.classList.remove('open');
        if (dom.formNuevoPaciente) dom.formNuevoPaciente.reset();

        state.pacienteEnEdicionID = null;

        await loadPatientsForAutocomplete();
        await loadPatientsDirectory(dni);
      } else {
        showToast(result.message || 'Error al guardar paciente', 'error');
      }
    } catch (err) {
      console.error('Error registrando paciente:', err);
      showToast('Error de comunicacion con SQL Server', 'error');
    }
  }

  // ==========================================
  // MODULO 4: 📈 REPORTES Y ESTADISTICAS
  // ==========================================
  async function generateReport() {
    if (!dom.tbodyReporte) return;

    const fInicio = dom.repFechaInicio ? dom.repFechaInicio.value : '';
    const fFin = dom.repFechaFin ? dom.repFechaFin.value : '';
    const servId = dom.repServicio ? dom.repServicio.value : '0';
    const estado = dom.repEstado ? dom.repEstado.value : '';

    dom.tbodyReporte.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:#94a3b8;">Generando reporte estadistico desde SQL Server...</td></tr>';

    try {
      const params = new URLSearchParams({
        fecha_inicio: fInicio,
        fecha_fin: fFin,
        servicio_id: servId,
        estado: estado
      });

      const res = await fetch(`api/reportes.php?${params.toString()}`);
      const data = await res.json();

      if (!data.success) {
        dom.tbodyReporte.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:#dc2626;">Error al generar el reporte.</td></tr>';
        return;
      }

      state.currentReportData = data;

      // Actualizar tarjetas de resumen
      const resumen = data.resumen || {};
      const totalAnalisis = resumen.total_analisis ?? (data.total_analisis ?? 0);
      if (dom.repTotalAnalisis) dom.repTotalAnalisis.textContent = totalAnalisis;

      const porEstado = resumen.por_estado || data.por_estado || {};
      const validados = porEstado.VALIDADO || 0;
      const pendientes = porEstado.PENDIENTE || 0;
      const anulados = porEstado.ANULADO || 0;

      if (dom.repValidados) dom.repValidados.textContent = validados;
      if (dom.repPendientes) dom.repPendientes.textContent = pendientes;
      const repAnulados = dom.repAnulados || document.getElementById('repAnulados');
      if (repAnulados) repAnulados.textContent = anulados;

      // Llenar tabla de resultados
      const rows = data.data || [];
      dom.tbodyReporte.innerHTML = '';

      if (rows.length === 0) {
        dom.tbodyReporte.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:#94a3b8;">No se registraron analisis para el periodo y filtros seleccionados.</td></tr>';
        return;
      }

      rows.forEach(item => {
        const tr = document.createElement('tr');
        const fStr = item.FechaSolicitud ? item.FechaSolicitud.substring(0, 16).replace('T', ' ') : '-';
        const estadoActual = (item.Estado || 'PENDIENTE').toUpperCase();

        tr.innerHTML = `
          <td><strong>${escapeHtml(item.CodigoSolicitud || '#' + item.SolicitudID)}</strong></td>
          <td>${fStr}</td>
          <td><strong>${escapeHtml(item.NombreCompleto)}</strong></td>
          <td>${item.NumeroDocumento || '-'}</td>
          <td>${item.HistoriaClinica || '-'}</td>
          <td>${escapeHtml(item.ServicioNombre || '-')}</td>
          <td>${escapeHtml(item.MedicoCompleto || '-')}</td>
          <td>
            <select class="status-inline-select" onchange="cambiarEstadoSolicitud(${item.SolicitudID}, this.value)">
              <option value="VALIDADO" ${estadoActual === 'VALIDADO' ? 'selected' : ''}>VALIDADO</option>
              <option value="PENDIENTE" ${estadoActual === 'PENDIENTE' ? 'selected' : ''}>PENDIENTE</option>
              <option value="ANULADO" ${estadoActual === 'ANULADO' ? 'selected' : ''}>ANULADO</option>
            </select>
          </td>
          <td style="text-align:right;">
            <div class="table-actions-cell">
              <button type="button" class="btn-action-edit" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;" onclick="verSolicitud(${item.SolicitudID})">
                👁️ Ver
              </button>
              <button type="button" class="btn-action-edit" onclick="editarSolicitud(${item.SolicitudID})">
                ✏️ Editar
              </button>
              <button type="button" class="btn-action-delete" onclick="eliminarSolicitud(${item.SolicitudID}, '${item.CodigoSolicitud || '#' + item.SolicitudID}')">
                🗑️
              </button>
            </div>
          </td>
        `;
        dom.tbodyReporte.appendChild(tr);
      });

    } catch (err) {
      console.error('Error generando reporte:', err);
      dom.tbodyReporte.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:30px; color:#dc2626;">Error de conexion con el servidor.</td></tr>';
    }
  }

  function setReportDatePreset(type) {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const today = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;

    if (type === 'hoy') {
      dom.repFechaInicio.value = today;
      dom.repFechaFin.value = today;
    } else if (type === 'semana') {
      const d = new Date(now);
      d.setDate(d.getDate() - 7);
      dom.repFechaInicio.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
      dom.repFechaFin.value = today;
    } else if (type === 'mes') {
      dom.repFechaInicio.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-01`;
      dom.repFechaFin.value = today;
    } else if (type === 'anio') {
      dom.repFechaInicio.value = `${now.getFullYear()}-01-01`;
      dom.repFechaFin.value = today;
    }

    generateReport();
  }

  function exportReportCSV() {
    if (!state.currentReportData || !state.currentReportData.data || state.currentReportData.data.length === 0) {
      showToast('No hay datos para exportar en el reporte actual', 'error');
      return;
    }

    const rows = state.currentReportData.data;
    let csvContent = "\uFEFF";
    csvContent += "Codigo;Fecha;Paciente;DNI;HistoriaClinica;Servicio;Medico;Diagnostico;Estado\r\n";

    rows.forEach(r => {
      const linea = [
        r.CodigoSolicitud || '#' + r.SolicitudID,
        r.FechaSolicitud || '',
        `"${(r.NombreCompleto || '').replace(/"/g, '""')}"`,
        r.NumeroDocumento || '',
        r.HistoriaClinica || '',
        `"${(r.ServicioNombre || '').replace(/"/g, '""')}"`,
        `"${(r.MedicoCompleto || '').replace(/"/g, '""')}"`,
        `"${(r.DiagnosticoPresuntivo || '').replace(/"/g, '""')}"`,
        r.Estado || ''
      ];
      csvContent += linea.join(';') + "\r\n";
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `Reporte_Laboratorio_${new Date().toISOString().substring(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('Reporte exportado en formato CSV compatible con Excel', 'success');
  }

  function printReport() {
    if (!state.currentReportData || !state.currentReportData.data) {
      showToast('Primero genere un reporte para poder imprimirlo', 'warning');
      return;
    }

    const r = state.currentReportData;
    const rows = r.data || [];

    let html = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Reporte Consolidado de Laboratorio - Hospital Distrital Pacasmayo</title>
        <style>
          body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; color: #111; }
          .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
          .header h1 { font-size: 14px; margin: 0 0 4px 0; font-weight: bold; }
          .header h2 { font-size: 12px; margin: 0; color: #444; }
          .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 11px; }
          .summary { display: flex; gap: 20px; background: #f0f0f0; padding: 8px 12px; margin-bottom: 15px; border-radius: 4px; }
          .summary-item { font-size: 11px; }
          table { width: 100%; border-collapse: collapse; margin-top: 10px; }
          th, td { border: 1px solid #999; padding: 5px 7px; text-align: left; }
          th { background: #e5e5e5; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>GOBIERNO REGIONAL DE LA LIBERTAD &bull; RED INTEGRADA DE SALUD PACASMAYO</h1>
          <h2>HOSPITAL DISTRITAL PACASMAYO &bull; UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA</h2>
          <h2 style="font-weight:bold; margin-top:5px;">REPORTE CONSOLIDADO DE ATENCIONES DE LABORATORIO</h2>
        </div>

        <div class="meta">
          <div><strong>Periodo:</strong> ${dom.repFechaInicio.value} al ${dom.repFechaFin.value}</div>
          <div><strong>Fecha Emision Reporte:</strong> ${new Date().toLocaleString()}</div>
        </div>

        <div class="summary">
          <div class="summary-item"><strong>Total Analisis:</strong> ${r.total_analisis ?? 0}</div>
          <div class="summary-item"><strong>Pacientes Unicos:</strong> ${r.pacientes_unicos ?? 0}</div>
          <div class="summary-item"><strong>Validados:</strong> ${r.por_estado?.VALIDADO ?? 0}</div>
          <div class="summary-item"><strong>Pendientes:</strong> ${r.por_estado?.PENDIENTE ?? 0}</div>
          <div class="summary-item"><strong>Anulados:</strong> ${r.por_estado?.ANULADO ?? 0}</div>
        </div>

        <table>
          <thead>
            <tr>
              <th>Codigo</th>
              <th>Fecha y Hora</th>
              <th>Paciente</th>
              <th>DNI</th>
              <th>Historia Clinica</th>
              <th>Servicio Solicitante</th>
              <th>Medico Solicitante</th>
              <th>Diagnostico Presuntivo</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
    `;

    rows.forEach(row => {
      html += `
        <tr>
          <td>${escapeHtml(row.CodigoSolicitud || '#' + row.SolicitudID)}</td>
          <td>${row.FechaSolicitud || '-'}</td>
          <td><strong>${escapeHtml(row.NombreCompleto)}</strong></td>
          <td>${row.NumeroDocumento || '-'}</td>
          <td>${row.HistoriaClinica || '-'}</td>
          <td>${escapeHtml(row.ServicioNombre || '-')}</td>
          <td>${escapeHtml(row.MedicoCompleto || '-')}</td>
          <td>${escapeHtml(row.DiagnosticoPresuntivo || '-')}</td>
          <td>${escapeHtml(row.Estado || '-')}</td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
        <div style="margin-top:25px; display:flex; justify-content:flex-end;">
          <div style="text-align:center; border-top:1px solid #000; width:220px; padding-top:4px;">
            Responsable de Laboratorio Clinico
          </div>
        </div>
      </body>
      </html>
    `;

    const printWin = window.open('', '_blank');
    printWin.document.write(html);
    printWin.document.close();
    printWin.focus();
    setTimeout(() => {
      printWin.print();
    }, 250);
  }



  function filterHistoryByStatus(status) {
    if (!state.historialCompleto) return;
    if (status === 'todos') {
      renderHistoryTable(state.historialCompleto);
    } else {
      const filtered = state.historialCompleto.filter(h => (h.Estado || '').toUpperCase() === status.toUpperCase());
      renderHistoryTable(filtered);
    }
  }

  // ==========================================
  // MODULO 6: CATALOGO DE VALORES DE REFERENCIA
  // ==========================================
  function renderCatalog() {
    if (!dom.containerCatalogo) return;
    dom.containerCatalogo.innerHTML = '';

    if (!state.examenes || state.examenes.length === 0) {
      dom.containerCatalogo.innerHTML = '<p style="color:#94a3b8; text-align:center; padding:30px;">Cargando catalogo institucional...</p>';
      return;
    }

    state.examenes.forEach(ex => {
      const card = document.createElement('div');
      card.className = 'catalog-card';

      let html = `
        <div class="catalog-card-header">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px; vertical-align:middle;"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
          ${escapeHtml(ex.Nombre)}
        </div>
        <table class="catalog-table">
          <thead>
            <tr>
              <th style="width:35%;">Parametro / Analisis</th>
              <th style="width:15%;">Unidad</th>
              <th style="width:50%;">Valores de Referencia Clinicos</th>
            </tr>
          </thead>
          <tbody>
      `;

      if (ex.Parametros && ex.Parametros.length > 0) {
        ex.Parametros.forEach(p => {
          let refHtml = '<span class="ref-badge-muted">No disponible</span>';

          if (p.ValoresReferencia && p.ValoresReferencia.length > 0) {
            refHtml = p.ValoresReferencia.map(vr => {
              let sexBadge = '';
              if (vr.SexoCodigo === 'M') {
                sexBadge = '<span class="ref-sex-tag ref-sex-m">Hombres:</span> ';
              } else if (vr.SexoCodigo === 'F') {
                sexBadge = '<span class="ref-sex-tag ref-sex-f">Mujeres:</span> ';
              }

              let valText = '';
              if (vr.TextoReferencia) {
                valText = escapeHtml(vr.TextoReferencia);
              } else if (vr.ValorMinimo !== null && vr.ValorMaximo !== null) {
                valText = `${vr.ValorMinimo} - ${vr.ValorMaximo} ${escapeHtml(p.UnidadSimbolo || '')}`;
              } else if (vr.ValorMinimo !== null) {
                valText = `>= ${vr.ValorMinimo} ${escapeHtml(p.UnidadSimbolo || '')}`;
              } else if (vr.ValorMaximo !== null) {
                valText = `< ${vr.ValorMaximo} ${escapeHtml(p.UnidadSimbolo || '')}`;
              } else {
                valText = 'Normal';
              }

              return `<div class="ref-val-line">${sexBadge}<span class="ref-val-highlight">${valText}</span></div>`;
            }).join('');
          }

          html += `
            <tr>
              <td><strong class="param-title">${escapeHtml(p.Nombre)}</strong></td>
              <td><span class="unit-pill">${escapeHtml(p.UnidadSimbolo || '-')}</span></td>
              <td>${refHtml}</td>
            </tr>
          `;
        });
      } else {
        html += '<tr><td colspan="3" style="text-align:center; color:#94a3b8; padding:15px;">Sin parametros configurados.</td></tr>';
      }

      html += '</tbody></table>';
      card.innerHTML = html;
      dom.containerCatalogo.appendChild(card);
    });
  }

  // ==========================================
  // SELECCION Y AUTOCOMPLETADO DE PACIENTES
  // ==========================================
  function selectPatient(patient) {
    state.pacienteActual = patient;

    if (dom.txtApellidos) {
      dom.txtApellidos.value = trim((patient.ApellidoPaterno || '') + ' ' + (patient.ApellidoMaterno || ''));
    }
    if (dom.txtNombres) {
      dom.txtNombres.value = patient.Nombres || '';
    }
    if (dom.pacienteDni) dom.pacienteDni.value = patient.NumeroDocumento || '';
    if (dom.pacienteHc) dom.pacienteHc.value = patient.HistoriaClinica || '';
    if (dom.pacienteEdad) dom.pacienteEdad.value = patient.Edad !== null ? patient.Edad : '';

    if (patient.SexoCodigo === 'F') {
      if (dom.radioSexoF) dom.radioSexoF.checked = true;
    } else {
      if (dom.radioSexoM) dom.radioSexoM.checked = true;
    }

    if (dom.txtBuscarPaciente) {
      dom.txtBuscarPaciente.value = patient.NombreCompleto || trim((patient.ApellidoPaterno || '') + ' ' + (patient.ApellidoMaterno || '') + ' ' + (patient.Nombres || ''));
    }

    reevaluateAllRanges();
    showToast(`Paciente seleccionado: ${patient.NombreCompleto || patient.Nombres}`, 'success');
  }

  function trim(str) {
    return (str || '').trim();
  }

  // ==========================================
  // EVALUACION DE RANGOS DE REFERENCIA
  // ==========================================
  function evaluateResultRange(input) {
    const pId = input.dataset.paramId;
    if (!pId || !state.parametrosMap[pId]) return;

    const valStr = input.value.trim();
    input.classList.remove('val-normal', 'val-abnormal-high', 'val-abnormal-low');

    if (valStr === '') return;

    const valNum = parseFloat(valStr.replace(',', '.'));
    if (isNaN(valNum)) return;

    const paramData = state.parametrosMap[pId];
    const refs = paramData.ValoresReferencia || [];
    if (refs.length === 0) return;

    const sexoActual = (dom.radioSexoF && dom.radioSexoF.checked) ? 'F' : 'M';
    let matchedRef = refs.find(r => r.SexoCodigo === sexoActual);
    if (!matchedRef) {
      matchedRef = refs.find(r => !r.SexoCodigo);
    }
    if (!matchedRef) matchedRef = refs[0];

    if (matchedRef) {
      const min = matchedRef.ValorMinimo;
      const max = matchedRef.ValorMaximo;

      if (min !== null && valNum < min) {
        input.classList.add('val-abnormal-low');
      } else if (max !== null && valNum > max) {
        input.classList.add('val-abnormal-high');
      } else if (min !== null || max !== null) {
        input.classList.add('val-normal');
      }
    }
  }

  function reevaluateAllRanges() {
    document.querySelectorAll('.res-input').forEach(input => {
      evaluateResultRange(input);
    });
  }

  // ==========================================
  // GUARDAR O ACTUALIZAR INFORME EN SQL SERVER
  // ==========================================
  async function saveLabReport() {
    let pacienteId = state.pacienteActual ? state.pacienteActual.PacienteID : null;

    const apellidos = dom.txtApellidos ? dom.txtApellidos.value.trim() : '';
    const nombres = dom.txtNombres ? dom.txtNombres.value.trim() : '';
    const dni = dom.pacienteDni ? dom.pacienteDni.value.trim() : '';

    if (!pacienteId) {
      if (!apellidos || !nombres) {
        showToast('Debe ingresar los Apellidos y Nombres del paciente', 'error');
        if (dom.txtApellidos) dom.txtApellidos.focus();
        return;
      }

      // Separar apellido paterno y materno
      const partesAp = apellidos.split(' ');
      const paterno = partesAp[0] || 'PACIENTE';
      const materno = partesAp.slice(1).join(' ');

      try {
        const resPac = await fetch('api/pacientes.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            NumeroDocumento: dni,
            Nombres: nombres,
            ApellidoPaterno: paterno,
            ApellidoMaterno: materno,
            SexoCodigo: (dom.radioSexoF && dom.radioSexoF.checked) ? 'F' : 'M',
            Edad: parseInt(dom.pacienteEdad ? dom.pacienteEdad.value : '0') || null,
            HistoriaClinica: dom.pacienteHc ? dom.pacienteHc.value.trim() : ''
          })
        });
        const pacJson = await resPac.json();
        if (pacJson.success) {
          pacienteId = pacJson.data.PacienteID;
          state.pacienteActual = pacJson.data;
          await loadPatientsForAutocomplete();
        } else {
          showToast(`Error al registrar paciente: ${pacJson.message}`, 'error');
          return;
        }
      } catch (e) {
        showToast('Error al conectar con SQL Server para registrar paciente', 'error');
        return;
      }
    }

    // Resolver ID de servicio desde texto escrito
    let servicioId = 1;
    const servTexto = dom.txtServicio ? dom.txtServicio.value.trim() : '';
    if (servTexto) {
      const matchServ = state.servicios.find(s => s.Nombre.toLowerCase() === servTexto.toLowerCase());
      if (matchServ) servicioId = matchServ.ServicioID;
    }

    // Resolver ID de medico desde texto escrito
    let medicoId = 1;
    const medTexto = dom.txtMedico ? dom.txtMedico.value.trim() : '';
    if (medTexto) {
      const matchMed = state.medicos.find(m => m.NombreCompleto.toLowerCase() === medTexto.toLowerCase());
      if (matchMed) medicoId = matchMed.PersonalID;
    }

    // Recolectar valores ingresados
    const valores = {};
    let totalIngresados = 0;
    document.querySelectorAll('.res-input').forEach(input => {
      const pId = input.dataset.paramId;
      const val = input.value.trim();
      if (pId && val !== '') {
        valores[pId] = val;
        totalIngresados++;
      }
    });

    const isEditMode = state.solicitudIdEnEdicion !== null;
    const estadoSeleccionado = dom.cboEstadoSolicitud ? dom.cboEstadoSolicitud.value : 'VALIDADO';

    const payload = {
      action: isEditMode ? 'update' : 'create',
      solicitud_id: state.solicitudIdEnEdicion,
      estado: estadoSeleccionado,
      paciente_id: pacienteId,
      personal_solicitante_id: medicoId,
      servicio_solicitante_id: servicioId,
      fecha_toma: dom.txtFechaToma && dom.txtFechaToma.value ? dom.txtFechaToma.value.replace('T', ' ') + ':00' : null,
      fecha_emision: dom.txtFechaEmision && dom.txtFechaEmision.value ? dom.txtFechaEmision.value.replace('T', ' ') + ':00' : null,
      diagnostico_presuntivo: dom.txtDiagnostico ? dom.txtDiagnostico.value.trim() : '',
      otros_examenes: dom.txtOtrosExamenes ? dom.txtOtrosExamenes.value.trim() : '',
      observaciones: dom.txtObservaciones ? dom.txtObservaciones.value.trim() : '',
      valores: valores
    };

    if (dom.btnGuardar) {
      dom.btnGuardar.disabled = true;
      dom.btnGuardar.innerHTML = isEditMode ? 'Actualizando...' : 'Guardando...';
    }

    try {
      const res = await fetch('api/resultados.php', {
        method: isEditMode ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        const msg = isEditMode
          ? `¡Solicitud ${data.data?.CodigoMuestra || '#' + state.solicitudIdEnEdicion} actualizada exitosamente!`
          : `¡Informe guardado con exito! Codigo: ${data.data.CodigoMuestra} (${data.data.TotalParametros} parametros)`;

        showToast(msg, 'success');

        if (isEditMode) {
          cancelarEdicion();
        }

        loadDashboardData();
        if (state.currentTab === 'viewReportes') {
          generateReport();
        }
      } else {
        showToast(`Error al guardar: ${data.message || data.error}`, 'error');
      }
    } catch (err) {
      console.error('Error guardando:', err);
      showToast('Error al conectar con la base de datos', 'error');
    } finally {
      if (dom.btnGuardar) {
        dom.btnGuardar.disabled = false;
        dom.btnGuardar.innerHTML = `
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Guardar en BD
        `;
      }
    }
  }

  // ==========================================
  // EDITAR SOLICITUD DE ANALISIS
  // ==========================================
  async function editarSolicitud(solicitudId) {
    if (!solicitudId) return;

    try {
      showToast(`Cargando solicitud #${solicitudId} para edicion...`, 'info');
      const res = await fetch(`api/resultados.php?solicitud_id=${solicitudId}`);
      const result = await res.json();

      if (!result.success || !result.data) {
        showToast('No se pudo cargar la solicitud para edicion', 'error');
        return;
      }

      const rep = result.data;
      state.solicitudIdEnEdicion = parseInt(rep.SolicitudID);

      // Activar banner de modo edicion
      if (dom.bannerModoEdicion) {
        dom.bannerModoEdicion.style.display = 'flex';
      }
      if (dom.lblEditCodigo) {
        dom.lblEditCodigo.textContent = rep.CodigoMuestra || ('#' + rep.SolicitudID);
      }
      if (dom.btnGuardar) {
        dom.btnGuardar.innerHTML = `
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          Guardar Cambios
        `;
      }

      // Cargar datos del paciente
      state.pacienteActual = {
        PacienteID: rep.PacienteID,
        NumeroDocumento: rep.NumeroDocumento,
        Nombres: rep.Nombres,
        ApellidoPaterno: rep.ApellidoPaterno,
        ApellidoMaterno: rep.ApellidoMaterno,
        NombreCompleto: rep.PacienteNombreCompleto,
        SexoCodigo: rep.SexoCodigo,
        Edad: rep.Edad,
        HistoriaClinica: rep.HistoriaClinica
      };

      if (dom.txtApellidos) dom.txtApellidos.value = rep.Apellidos || trim((rep.ApellidoPaterno || '') + ' ' + (rep.ApellidoMaterno || ''));
      if (dom.txtNombres) dom.txtNombres.value = rep.Nombres || '';
      if (dom.pacienteDni) dom.pacienteDni.value = rep.NumeroDocumento || '';
      if (dom.pacienteHc) dom.pacienteHc.value = rep.HistoriaClinica || '';
      if (dom.pacienteEdad) dom.pacienteEdad.value = rep.Edad !== null ? rep.Edad : '';

      if (rep.SexoCodigo === 'F') {
        if (dom.radioSexoF) dom.radioSexoF.checked = true;
      } else {
        if (dom.radioSexoM) dom.radioSexoM.checked = true;
      }

      if (dom.txtBuscarPaciente) {
        dom.txtBuscarPaciente.value = rep.PacienteNombreCompleto || '';
      }

      // Cargar Servicio y Medico
      if (dom.txtServicio) dom.txtServicio.value = rep.ServicioNombre || '';
      if (dom.txtMedico) dom.txtMedico.value = rep.MedicoNombreCompleto || '';
      if (dom.cboEstadoSolicitud) dom.cboEstadoSolicitud.value = rep.SolicitudEstado || 'VALIDADO';

      if (rep.FechaToma && dom.txtFechaToma) {
        dom.txtFechaToma.value = rep.FechaToma.substring(0, 16);
      }
      if (rep.FechaEmision && dom.txtFechaEmision) {
        dom.txtFechaEmision.value = rep.FechaEmision.substring(0, 16);
      }

      if (dom.txtDiagnostico) dom.txtDiagnostico.value = rep.SolicitudObservacion || '';
      if (dom.txtObservaciones) dom.txtObservaciones.value = rep.Observaciones || '';

      // Limpiar y cargar valores de parametros
      document.querySelectorAll('.res-input').forEach(input => {
        input.value = '';
        input.classList.remove('val-normal', 'val-abnormal-high', 'val-abnormal-low');
      });

      if (rep.Valores) {
        for (const [pId, val] of Object.entries(rep.Valores)) {
          const inp = document.querySelector(`.res-input[data-param-id="${pId}"]`);
          if (inp) {
            inp.value = val;
            evaluateResultRange(inp);
          }
        }
      }

      switchTab('viewFormato');
      closeHistoryModal();
      showToast(`Modo edicion activado para ${rep.CodigoMuestra || '#' + rep.SolicitudID}`, 'success');

    } catch (err) {
      console.error('Error al entrar a modo edicion:', err);
      showToast('Error al cargar la solicitud para edicion', 'error');
    }
  }

  function cancelarEdicion() {
    state.solicitudIdEnEdicion = null;
    if (dom.bannerModoEdicion) {
      dom.bannerModoEdicion.style.display = 'none';
    }
    if (dom.btnGuardar) {
      dom.btnGuardar.innerHTML = `
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
        Guardar en BD
      `;
    }
  }

  // ==========================================
  // ELIMINAR SOLICITUD DE ANALISIS
  // ==========================================
  async function eliminarSolicitud(solicitudId, codigo) {
    if (!solicitudId) return;

    const label = codigo ? codigo : `solicitud #${solicitudId}`;
    if (!confirm(`¿Esta seguro de que desea ELIMINAR definitivamente la ${label} de la base de datos?\n\nEsta accion borrara la muestra y todos los resultados de parametros asociados.`)) {
      return;
    }

    try {
      const res = await fetch(`api/resultados.php?solicitud_id=${solicitudId}`, {
        method: 'DELETE'
      });
      const data = await res.json();

      if (data.success) {
        showToast(`Solicitud ${label} eliminada exitosamente`, 'success');

        if (state.solicitudIdEnEdicion === parseInt(solicitudId)) {
          cancelarEdicion();
          resetForm();
        }

        loadDashboardData();
        if (state.currentTab === 'viewReportes') {
          generateReport();
        }
      } else {
        showToast(`Error al eliminar: ${data.message || data.error}`, 'error');
      }
    } catch (err) {
      console.error('Error eliminando solicitud:', err);
      showToast('Error de conexion al intentar eliminar la solicitud', 'error');
    }
  }

  // ==========================================
  // CAMBIAR ESTADO RAPIDO DE SOLICITUD
  // ==========================================
  async function cambiarEstadoSolicitud(solicitudId, nuevoEstado) {
    if (!solicitudId || !nuevoEstado) return;

    try {
      const res = await fetch('api/resultados.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_status',
          solicitud_id: solicitudId,
          estado: nuevoEstado
        })
      });
      const data = await res.json();

      if (data.success) {
        showToast(`Estado actualizado a "${nuevoEstado}" para la solicitud #${solicitudId}`, 'success');
        loadDashboardData();
        generateReport();
      } else {
        showToast(`Error al actualizar estado: ${data.message}`, 'error');
      }
    } catch (err) {
      console.error('Error al actualizar estado:', err);
      showToast('Error al conectar con SQL Server', 'error');
    }
  }

  // ==========================================
  // IMPRIMIR FORMATO OFICIAL A4
  // ==========================================
  function printLabReport() {
    window.print();
  }

  // ==========================================
  // REINICIAR FORMULARIO
  // ==========================================
  function resetForm() {
    state.pacienteActual = null;
    cancelarEdicion();

    if (dom.txtBuscarPaciente) dom.txtBuscarPaciente.value = '';
    if (dom.txtApellidos) dom.txtApellidos.value = '';
    if (dom.txtNombres) dom.txtNombres.value = '';
    if (dom.pacienteDni) dom.pacienteDni.value = '';
    if (dom.pacienteEdad) dom.pacienteEdad.value = '';
    if (dom.pacienteHc) dom.pacienteHc.value = '';
    if (dom.radioSexoM) dom.radioSexoM.checked = true;
    if (dom.txtDiagnostico) dom.txtDiagnostico.value = '';
    if (dom.txtOtrosExamenes) dom.txtOtrosExamenes.value = '';
    if (dom.txtObservaciones) dom.txtObservaciones.value = '';
    if (dom.cboEstadoSolicitud) dom.cboEstadoSolicitud.value = 'VALIDADO';

    if (dom.txtMedico && state.medicoLogueado) {
      dom.txtMedico.value = state.medicoLogueado.NombreCompleto || state.medicoLogueado.NombreSimple;
    }

    document.querySelectorAll('.res-input').forEach(input => {
      input.value = '';
      input.classList.remove('val-normal', 'val-abnormal-high', 'val-abnormal-low');
    });

    setupDateTimeDefaults();
    showToast('Formato restablecido para un nuevo analisis clinico', 'success');
  }

  // ==========================================
  // CARGAR PACIENTE DE PRUEBA / DEMO
  // ==========================================
  async function loadDemoData() {
    try {
      const res = await fetch('api/pacientes.php');
      const data = await res.json();
      if (data.success && data.data.length > 0) {
        const demoPatient = data.data[0];
        selectPatient(demoPatient);

        // Llenar valores de prueba
        const demoValues = {
          '1': '92',     // Glucosa
          '2': '32',     // Urea
          '3': '1.05',   // Creatinina
          '4': '5.2',    // Acido Urico
          '5': '210',    // Colesterol total (elevado)
          '6': '48',     // HDL
          '7': '138',    // LDL (elevado)
          '8': '24',     // VLDL
          '9': '165',    // Trigliceridos (elevado)
          '10': '7.2',   // Proteina total
          '11': '4.1',   // Albumina
          '12': '1.8',   // Globulina
          '13': '28',    // Transaminasa TGO
          '14': '35',    // Transaminasa TGP
          '15': '0.85',  // Bilirrubina total
          '16': '0.18',  // Bilirrubina directa
          '17': '0.67',  // Bilirrubina indirecta
          '18': '115',   // Fosfatasa alcalina
          '19': '32',    // GGT
          '20': '12.8',  // Tiempo de protrombina
          '21': '1.05',  // INR
          '22': '90',    // Glucosa basal
          '23': '125',   // Glucosa 1 hora
          '24': '110',   // Glucosa 2 horas
          '26': '5.4',   // Hemoglobina glicosilada
          '27': '68',    // Amilasa
          '28': '34',    // Lipasa
        };

        for (const [pId, val] of Object.entries(demoValues)) {
          const inp = document.querySelector(`.res-input[data-param-id="${pId}"]`);
          if (inp) {
            inp.value = val;
            evaluateResultRange(inp);
          }
        }

        if (dom.txtDiagnostico) dom.txtDiagnostico.value = 'E11 - Diabetes mellitus no insulinodependiente / Control de rutina';
        if (dom.txtObservaciones) dom.txtObservaciones.value = 'Muestra procesada en analizador automatizado por duplicado. Suero no hemolizado ni lipemico.';
        if (dom.txtServicio) dom.txtServicio.value = 'UPSS PATOLOGIA CLINICA Y ANATOMIA PATOLOGICA';
        if (dom.txtMedico) dom.txtMedico.value = 'Morales Solis, Ricardo';

        showToast('Datos demo cargados para Juan Carlos Paredes', 'success');
      }
    } catch (err) {
      console.error('Error cargando demo:', err);
    }
  }

  // ==========================================
  // CARGAR INFORME ESPECIFICO DE LA BD
  // ==========================================
  async function loadSingleReport(solicitudId) {
    await editarSolicitud(solicitudId);
  }

  // ==========================================
  // MODAL HISTORIAL RAPIDO
  // ==========================================
  function closeHistoryModal() {
    if (dom.modalHistorial) dom.modalHistorial.classList.remove('open');
  }

  async function loadHistoryList(query = '') {
    if (!dom.tbodyHistorial) return;
    try {
      const res = await fetch(`api/resultados.php?limit=25&q=${encodeURIComponent(query)}`);
      const data = await res.json();

      if (!data.success || !data.data || data.data.length === 0) {
        dom.tbodyHistorial.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:18px; color:#94a3b8;">No se encontraron analisis.</td></tr>';
        return;
      }

      dom.tbodyHistorial.innerHTML = '';
      data.data.forEach(h => {
        const tr = document.createElement('tr');
        const fStr = h.FechaSolicitud ? h.FechaSolicitud.substring(0, 16).replace('T', ' ') : '-';
        tr.innerHTML = `
          <td><strong>${escapeHtml(h.CodigoMuestra || h.CodigoSolicitud || '#' + h.SolicitudID)}</strong></td>
          <td>${fStr}</td>
          <td><strong>${escapeHtml(h.NombreCompleto)}</strong></td>
          <td>${h.NumeroDocumento || '-'}</td>
          <td>${h.HistoriaClinica || '-'}</td>
          <td>${escapeHtml(h.ServicioNombre || 'LABORATORIO')}</td>
          <td style="text-align:right;">
            <div class="table-actions-cell">
              <button type="button" class="btn-action-edit" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;" onclick="verSolicitud(${h.SolicitudID})">
                👁️ Ver
              </button>
              <button type="button" class="btn-action-edit" onclick="editarSolicitud(${h.SolicitudID})">
                ✏️ Editar
              </button>
              <button type="button" class="btn-action-delete" onclick="eliminarSolicitud(${h.SolicitudID}, '${h.CodigoMuestra || '#' + h.SolicitudID}')">
                🗑️
              </button>
            </div>
          </td>
        `;
        dom.tbodyHistorial.appendChild(tr);
      });
    } catch (e) {
      dom.tbodyHistorial.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#dc2626;">Error cargando historial</td></tr>';
    }
  }

  // ==========================================
  // HELPERS GENERALES
  // ==========================================
  function getStatusBadge(status) {
    const s = (status || 'PENDIENTE').toUpperCase();
    if (s === 'VALIDADO') return '<span class="badge badge-validado">VALIDADO</span>';
    if (s === 'ANULADO' || s === 'RECHAZADA') return '<span class="badge badge-anulado">ANULADO</span>';
    return '<span class="badge badge-pendiente">PENDIENTE</span>';
  }

  function showToast(message, type = 'info') {
    if (!dom.toastContainer) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${escapeHtml(message)}</span>`;

    dom.toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  // ==========================================
  // VER FICHA DETALLADA DEL ANALISIS
  // ==========================================
  async function verSolicitud(solicitudId) {
    if (!solicitudId) return;

    const modal = document.getElementById('modalVerAnalisis');
    const body = document.getElementById('bodyModalVerAnalisis');
    const lblCod = document.getElementById('lblModalVerCodigo');
    const btnEdit = document.getElementById('btnModalVerEditar');
    if (!modal || !body) return;

    modal.style.display = 'flex';
    body.innerHTML = '<div style="text-align:center; padding:35px; color:#94a3b8;">Cargando ficha del analisis desde la base de datos...</div>';

    try {
      const res = await fetch(`api/resultados.php?solicitud_id=${solicitudId}`);
      const data = await res.json();

      if (!data.success || !data.data) {
        body.innerHTML = '<div style="text-align:center; padding:30px; color:#ef4444;">No se pudo cargar la informacion del analisis.</div>';
        return;
      }

      const rep = data.data;
      if (lblCod) lblCod.textContent = rep.CodigoMuestra || ('#' + rep.SolicitudID);

      const sexoStr = rep.SexoCodigo === 'F' ? 'Femenino' : (rep.SexoCodigo === 'M' ? 'Masculino' : 'No especificado');
      const edadStr = rep.Edad !== null && rep.Edad !== undefined ? `${rep.Edad} años` : 'No registrada';
      const statusBadge = getStatusBadge(rep.SolicitudEstado);

      let paramsHTML = '';
      if (rep.Valores && Object.keys(rep.Valores).length > 0) {
        paramsHTML = `
          <table class="modern-table" style="margin-top: 15px;">
            <thead>
              <tr style="background:#f1f5f9;">
                <th style="width:45%;">Examen / Parametro</th>
                <th style="width:25%;">Resultado Medido</th>
                <th style="width:30%;">Estado en BD</th>
              </tr>
            </thead>
            <tbody>
        `;

        for (const [pId, val] of Object.entries(rep.Valores)) {
          const paramInfo = state.parametrosMap ? state.parametrosMap[pId] : null;
          const name = paramInfo ? paramInfo.Nombre : `Parametro #${pId}`;
          const unit = paramInfo ? (paramInfo.UnidadSimbolo || '') : '';

          paramsHTML += `
            <tr>
              <td><strong>${escapeHtml(name)}</strong></td>
              <td><span style="font-weight:700; color:#0284c7; font-size:14px;">${escapeHtml(val)}</span> ${escapeHtml(unit)}</td>
              <td><span class="badge badge-validado">REGISTRADO</span></td>
            </tr>
          `;
        }

        paramsHTML += '</tbody></table>';
      } else {
        paramsHTML = '<div style="padding:15px; background:#fffbeb; border:1px solid #fef3c7; border-radius:6px; color:#b45309; text-align:center; margin-top:15px;">Este informe aun no registra parametros guardados.</div>';
      }

      body.innerHTML = `
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; background:#f8fafc; padding:14px; border-radius:8px; border:1px solid #e2e8f0;">
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">PACIENTE:</span><div style="font-weight:700; color:#0f172a;">${escapeHtml(rep.PacienteNombreCompleto)}</div></div>
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">DNI:</span><div style="font-weight:600;">${escapeHtml(rep.NumeroDocumento || '-')}</div></div>
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">HISTORIA CLINICA:</span><div style="font-weight:600;">${escapeHtml(rep.HistoriaClinica || '-')}</div></div>
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">SEXO / EDAD:</span><div style="font-weight:600;">${sexoStr} (${edadStr})</div></div>
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">FECHA MUESTRA:</span><div style="font-weight:600;">${rep.FechaToma || rep.FechaSolicitud || '-'}</div></div>
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">MEDICO SOLICITANTE:</span><div style="font-weight:600;">${escapeHtml(rep.MedicoNombreCompleto || '-')}</div></div>
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">SERVICIO:</span><div style="font-weight:600;">${escapeHtml(rep.ServicioNombre || '-')}</div></div>
          <div><span style="font-size:11px; color:#64748b; font-weight:700;">ESTADO SOLICITUD:</span><div>${statusBadge}</div></div>
        </div>

        <div style="margin-top:16px;">
          <h4 style="font-size:14px; font-weight:700; color:#1e293b; margin-bottom:8px;">🔬 RESULTADOS DE ANALISIS DE LABORATORIO</h4>
          ${paramsHTML}
        </div>

        ${rep.SolicitudObservacion ? `
          <div style="margin-top:14px; padding:10px 14px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:6px; font-size:12.5px;">
            <strong>Diagnostico Presuntivo / Observacion:</strong> ${escapeHtml(rep.SolicitudObservacion)}
          </div>` : ''}
      `;

      if (btnEdit) {
        btnEdit.onclick = () => {
          modal.style.display = 'none';
          editarSolicitud(solicitudId);
        };
      }

    } catch (e) {
      console.error('Error al ver ficha:', e);
      body.innerHTML = '<div style="text-align:center; padding:30px; color:#ef4444;">Error de comunicacion con el servidor.</div>';
    }
  }

  function cerrarModalVerAnalisis() {
    const modal = document.getElementById('modalVerAnalisis');
    if (modal) modal.style.display = 'none';
  }

  // Bindings globales para eventos inline
  window.verSolicitud = verSolicitud;
  window.editarSolicitud = editarSolicitud;
  window.abrirModalEditarPaciente = abrirModalEditarPaciente;

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
});
