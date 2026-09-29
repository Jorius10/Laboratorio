<?php
$file = __DIR__ . '/../assets/js/laboratorio.js';
$js = file_get_contents($file);

$pos1 = strpos($js, 'function filterHistoryByStatus(status)');
$pos2 = strpos($js, 'function selectPatient(patient)');

if ($pos1 !== false && $pos2 !== false && $pos2 > $pos1) {
    $before = substr($js, 0, $pos1);
    $after = substr($js, $pos2);

    $cleanFunc = <<<'JS'
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
JS;

    file_put_contents($file, $before . $cleanFunc . "\n  " . $after);
    echo "SUCCESS_JS_FIXED\n";
} else {
    echo "ERROR_FINDING_MARKERS\n";
}
