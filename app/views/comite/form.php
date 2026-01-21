<?php require __DIR__ . '/../layout/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<style>
    .bloque-caso {
        background: #f1f1f1;
        border-left: 5px solid #0d6efd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .bloque-header {
        background: #eef5ff;
        border: 1px solid #d3e2ff;
        padding: 15px;
        border-radius: 8px;
    }
    .titulo-caso {
        font-weight: bold;
        color: #0d6efd;
        margin-bottom: 10px;
    }
    textarea { resize: none; }
</style>

<div class="container mt-4">

    <h3 class="mb-3">📋 Registro de Comité de Créditos</h3>

    <!-- ============================= -->
    <!-- ENCABEZADO DEL COMITÉ         -->
    <!-- ============================= -->
    <div class="bloque-header" id="panel-encabezado">

        <div class="row g-3 align-items-end">

            <div class="col-md-2">
                <label class="form-label"><b>Fecha</b></label>
                <input type="date" id="fecha" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label"><b>Hora</b></label>
                <input type="time" id="hora" class="form-control" value="<?= date('H:i') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label"><b>Agencia</b></label>
                <select id="agencia" class="form-select">
                    <option value="">Seleccione</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label"><b>Oficial Participante 1</b></label>
                <select id="oficial1" class="form-select">
                    <option value="">Seleccione</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label"><b>Oficial Participante 2</b></label>
                <select id="oficial2" class="form-select">
                    <option value="">Seleccione</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label"><b>Jefe de Agencia</b></label>
                <select id="jefe_ag" class="form-select">
                    <option value="">Seleccione</option>
                </select>
            </div>

        </div>

        <div class="mt-3 d-flex gap-2">
            <button id="btnEmpezar" class="btn btn-primary">
                ➡ Empezar
            </button>

            <button id="btnAñadir" class="btn btn-success" disabled>
                ➕ Añadir Caso
            </button>
        </div>

    </div>


    <hr>

    <!-- ============================= -->
    <!-- CONTENEDOR DE CASOS          -->
    <!-- ============================= -->
    <div id="contenedor-casos"></div>

    <div class="d-flex justify-content-center align-items-center gap-3 mt-4">
        <a href="index.php?url=dashboard" class="btn btn-outline-secondary">
            ← Volver
        </a>

        <button class="btn btn-dark px-4" id="btnFinalizar" disabled>
            ✔ Finalizar Comité
        </button>
    </div>

</div>

<!-- ============================= -->
<!-- PLANTILLA DEL CASO            -->
<!-- ============================= -->
<template id="plantilla-caso">
    <div class="bloque-caso caso-item">

        <h5 class="titulo-caso">Caso 01</h5>

        <div class="row g-3">

            <div class="col-md-2">
                <label class="form-label">DNI</label>
                <input type="text" class="form-control dni">
            </div>

            <div class="col-md-2">
                <label class="form-label">Cadena</label>
                <input type="text" class="form-control cadena">
            </div>

            <div class="col-md-4">
                <label class="form-label">Apellidos y Nombres</label>
                <input type="text" class="form-control nombres">
            </div>

            <div class="col-md-4">
                <label class="form-label">Comentarios</label>
                <textarea class="form-control comentarios" rows="3"></textarea>
            </div>

            <div class="col-md-3">
                <label class="form-label">Monto Propuesto</label>
                <input type="text" class="form-control monto" step="0.01">
            </div>

            <div class="col-md-3">
                <label class="form-label">Tipo Cli</label>
                <input type="text" class="form-control tipo_cli">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo Credito</label>
                <input type="text" class="form-control tipo_credito">
            </div>

            <div class="col-md-3">
                <label class="form-label">Oficial Proponente</label>
                <select class="form-select oficial_prop">
                    <option value="" selected disabled>Seleccione</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><b>Criterio</b></label>
                <select class="form-select criterio" required>
                    <option value="">Seleccione</option>
                    <!-- Se llenará por JS: C1..C9 (value=id_criterio, texto=codigo) -->
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Decisión</label>
                <select class="form-select decision">
                    <option value="Aprobado">Aprobado</option>
                    <option value="Observado">Observado</option>
                    <option value="Denegado">Denegado</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold text-primary">Riesgo Vinculado C-8</label>
                <select class="form-select sel-riesgo-c8">
                    <option value="No">No</option>
                    <option value="Si">Si</option>
                </select>
            </div>

        </div>

        <!-- Campo oculto para almacenar los vinculados -->
        <input type="hidden" class="rv_json" value="[]">

    </div>
</template>

<!-- =============================================================== -->
<!--  MODAL RIESGO VINCULADO                                         -->
<!-- =============================================================== -->
<div class="modal fade" id="modalRiesgoVinculado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">

          <div class="modal-header" style="background:#006548; color:white;">
              <h5 class="modal-title">Datos del Riesgo Vinculado</h5>
              <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

              <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0 text-primary">Vinculados del Caso</h6>
                  <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarVinc">
                      ➕ Añadir Vinculado
                  </button>
              </div>

              <div id="contenedorVinculados"></div>

              <template id="tpl-vinculado">
                  <div class="border rounded p-3 mb-3 fila-vinculado">

                      <div class="d-flex justify-content-between align-items-center mb-2">
                          <span class="fw-bold text-primary">
                              Vinculado <span class="num-vinc"></span>
                          </span>
                          <button type="button" class="btn btn-sm btn-outline-danger btnEliminarVinc">
                              ✖ Quitar
                          </button>
                      </div>

                      <div class="row g-2 mb-2">
                          <div class="col-md-2">
                              <label class="form-label">DNI</label>
                              <input type="text" class="form-control rv_dni">
                          </div>
                          <div class="col-md-4">
                              <label class="form-label">Apellidos</label>
                              <input type="text" class="form-control rv_apellidos">
                          </div>
                          <div class="col-md-4">
                              <label class="form-label">Nombres</label>
                              <input type="text" class="form-control rv_nombres">
                          </div>
                          <div class="col-md-2">
                              <label class="form-label">Grado Consanguinidad</label>
                              <select class="form-select rv_grado">
                                  <option value="">Seleccione</option>
                                  <option value="Primer Grado">Primer Grado</option>
                                  <option value="Segundo Grado">Segundo Grado</option>
                                  <option value="Tercer Grado">Tercer Grado</option>
                                  <option value="Cuarto Grado">Cuarto Grado</option>
                              </select>
                          </div>
                      </div>

                      <div class="row g-2">
                          <div class="col-md-4">
                              <label class="form-label">Domicilio</label>
                              <div class="d-flex gap-2">
                                  <select class="form-select rv_dom_si" style="max-width:80px;">
                                      <option value="No">No</option>
                                      <option value="Si">Si</option>
                                  </select>
                                  <input type="text" class="form-control rv_dom_txt" placeholder="Dirección">
                              </div>
                          </div>

                          <div class="col-md-4">
                              <label class="form-label">Actividad</label>
                              <div class="d-flex gap-2">
                                  <select class="form-select rv_act_si" style="max-width:80px;">
                                      <option value="No">No</option>
                                      <option value="Si">Si</option>
                                  </select>
                                  <input type="text" class="form-control rv_act_txt" placeholder="Ingrese Actividad">
                              </div>
                          </div>

                          <div class="col-md-4">
                              <label class="form-label">Predio Pecuario / Agrícola</label>
                              <div class="d-flex gap-2">
                                  <select class="form-select rv_pre_si" style="max-width:80px;">
                                      <option value="No">No</option>
                                      <option value="Si">Si</option>
                                  </select>
                                  <input type="text" class="form-control rv_pre_txt" placeholder="Detalles del predio">
                              </div>
                          </div>
                      </div>

                  </div>
              </template>

          </div>

          <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button id="btnGuardarRV" class="btn btn-success">Registrar Datos</button>
          </div>

      </div>
  </div>
</div>

<!-- =============================================================== -->
<!--  MODAL CONFIRMACIÓN ACTA                                          -->
<!-- =============================================================== -->
<div class="modal fade" id="modalDescargaActa" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

          <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Acta generada correctamente</h5>
          </div>

          <div class="modal-body text-center">
              <p class="mb-3">Seleccione el formato en el que desea descargar el acta:</p>

              <button id="btnWord" class="btn btn-success w-75 mb-2">
                  📄 Descargar en Word (.docx)
              </button>

              <button id="btnPdf" class="btn btn-danger w-75">
                  📕 Descargar en PDF (.pdf)
              </button>

              <div class="text-center mt-3">
                  <button id="btnCerrarModal" class="btn btn-secondary px-4">
                      Cerrar
                  </button>
              </div>

          </div>

      </div>
  </div>
</div>

<!-- =============================================================== -->
<!--  MODAL MODALIDAD DEL COMITÉ                                      -->
<!-- =============================================================== -->
<div class="modal fade" id="modalTipoComite" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Registro de Modalidad del Comité</h5>
            </div>

            <div class="modal-body">

                <h5 class="fw-bold">Número de Comité: <span id="numComite"></span></h5>

                <table class="table table-bordered text-center mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Participante</th>
                            <th>Presencial</th>
                            <th>Virtual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Oficial Proponente</td>
                            <td><input type="radio" name="modalidad_proponente" value="Presencial"></td>
                            <td><input type="radio" name="modalidad_proponente" value="Virtual"></td>
                        </tr>

                        <tr>
                            <td>Oficial Participante 1</td>
                            <td><input type="radio" name="modalidad_participante1" value="Presencial"></td>
                            <td><input type="radio" name="modalidad_participante1" value="Virtual"></td>
                        </tr>

                        <tr>
                            <td>Oficial Participante 2</td>
                            <td><input type="radio" name="modalidad_participante2" value="Presencial"></td>
                            <td><input type="radio" name="modalidad_participante2" value="Virtual"></td>
                        </tr>

                        <tr>
                            <td>Jefe de Agencia</td>
                            <td><input type="radio" name="modalidad_jefe" value="Presencial"></td>
                            <td><input type="radio" name="modalidad_jefe" value="Virtual"></td>
                        </tr>

                        <tr>
                            <td>Oficial de Riesgos</td>
                            <td><input type="radio" name="modalidad_riesgo" value="Presencial"></td>
                            <td><input type="radio" name="modalidad_riesgo" value="Virtual"></td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-center mt-3">
                    <button id="btnPresencial" class="btn btn-success px-4 me-2" disabled>Presencial</button>
                    <button id="btnVirtual" class="btn btn-primary px-4 me-2" disabled>Virtual</button>
                    <button id="btnSemi" class="btn btn-warning px-4" disabled>Semi Presencial</button>
                </div>

            </div>

        </div>
    </div>
</div>
<!-- =============================================================== -->
<!--  MODAL RESUMEN ANTES DE FINALIZAR                               -->
<!-- =============================================================== -->
<div class="modal fade" id="modalResumenComite"
     tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Resumen del Comité</h5>
      </div>

      <div class="modal-body">
        <div id="resumenErrores" class="alert alert-danger d-none">
          Complete los campos obligatorios antes de continuar.
        </div>

        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>DNI</th>
                <th>Cliente</th>
                <th class="text-end">Monto</th>
                <th>Tipo Cli</th>
                <th>Criterio</th>
                <th>Decisión</th>
              </tr>
            </thead>
            <tbody id="resumenBody"></tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <!-- ✅ Regresar: NO submit -->
        <button type="button" class="btn btn-outline-secondary" id="btnRegresarResumen">
          ← Regresar
        </button>

        <button type="button" class="btn btn-success" id="btnContinuarFinalizacion">
          Continuar →
        </button>
      </div>

    </div>
  </div>
</div>



<!-- ============================= -->
<!-- SCRIPTS DEL FORMULARIO        -->
<!-- ============================= -->
<script src="assets/js/global-alerts.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/validacion.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/comite_form.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/comite_riesgo_validacion.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/comite_modalidad.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/comite_riesgo.js?v=<?php echo time(); ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const btnFinalizar = document.getElementById("btnFinalizar");
  const resumenBody = document.getElementById("resumenBody");
  const resumenErrores = document.getElementById("resumenErrores");
  const btnContinuar = document.getElementById("btnContinuarFinalizacion");

  function badgeCriterio(text) {
    return `<span class="badge bg-primary">${text || '-'}</span>`;
  }

  function badgeDecision(text) {
    const t = (text || '').toLowerCase();
    let cls = 'bg-secondary';
    if (t === 'aprobado') cls = 'bg-success';
    if (t === 'observado') cls = 'bg-warning text-dark';
    if (t === 'denegado') cls = 'bg-danger';
    return `<span class="badge ${cls}">${text || '-'}</span>`;
  }

  function fmtMonto(val) {
    const n = parseFloat((val || '').toString().replace(/,/g,''));
    if (isNaN(n)) return '-';
    return n.toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  function construirResumen() {
    resumenBody.innerHTML = '';
    resumenErrores.classList.add('d-none');
    btnContinuar.disabled = false;

    const casos = document.querySelectorAll("#contenedor-casos .caso-item");
    let hayErrores = false;

    casos.forEach((caso, idx) => {
      const dni = caso.querySelector(".dni")?.value?.trim() || '';
      const cliente = caso.querySelector(".nombres")?.value?.trim() || '';
      const monto = caso.querySelector(".monto")?.value?.trim() || '';
      const tipoCli = caso.querySelector(".tipo_cli")?.value?.trim() || '';

      const selCriterio = caso.querySelector(".criterio");
      const criterioId = selCriterio?.value || '';
      const criterioTxt = selCriterio?.selectedOptions?.[0]?.textContent?.trim() || '';

      const selDecision = caso.querySelector(".decision");
      const decisionTxt = selDecision?.value?.trim() || '';

      // Validación mínima (criterio obligatorio + decisión obligatoria)
      if (!criterioId || !decisionTxt) hayErrores = true;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><b>${idx + 1}</b></td>
        <td>${dni ? dni : '<span class="text-muted">—</span>'}</td>
        <td>${cliente ? cliente : '<span class="text-muted">—</span>'}</td>
        <td class="text-end">${fmtMonto(monto)}</td>
        <td>${tipoCli ? tipoCli : '<span class="text-muted">—</span>'}</td>
        <td>${badgeCriterio(criterioTxt)}</td>
        <td>${badgeDecision(decisionTxt)}</td>
      `;
      resumenBody.appendChild(tr);
    });

    if (hayErrores) {
      resumenErrores.classList.remove('d-none');
      btnContinuar.disabled = true;
    }
  }

  // Mostrar modal resumen en vez de finalizar directo
  btnFinalizar?.addEventListener("click", (e) => {
    e.preventDefault();
    construirResumen();

    const modal = new bootstrap.Modal(document.getElementById('modalResumenComite'));
    modal.show();
  });

  // Aquí conectaremos a tu lógica real de finalizar (cuando me pases comite_form.js)
  btnContinuar?.addEventListener("click", () => {
    // Por ahora: solo cierra modal.
    // En el siguiente paso: llamaremos a tu función real de finalización.
    const modalEl = document.getElementById('modalResumenComite');
    const instance = bootstrap.Modal.getInstance(modalEl);
    if (instance) instance.hide();
  });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>