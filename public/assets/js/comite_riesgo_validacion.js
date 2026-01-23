/* ==================================================================================
   RIESGO VINCULADO (C-8) — Modal, Validaciones y Múltiples Filas
   Archivo unificado: comite_riesgo_vinculado.js

   ✅ Abre modal SOLO cuando tú lo llames:
      window.abrirModalRiesgoVinculado(caso)

   ✅ Guarda vinculados en:
      caso.querySelector(".rv_json").value = JSON.stringify(vinculados)

   ✅ Evita duplicidad de listeners con dataset flags.
  =================================================================================== */
  (function () {
    "use strict";

    // Guard básico: si no existe el modal, no hace nada.
    const modalElem = document.getElementById("modalRiesgoVinculado");
    if (!modalElem) return;

    const contVinc = document.getElementById("contenedorVinculados");
    const tplVinc = document.getElementById("tpl-vinculado");
    const btnGuardar = document.getElementById("btnGuardarRV");
    const btnAgregar = document.getElementById("btnAgregarVinc");

    if (!contVinc || !tplVinc || !btnGuardar) return;

    // Bootstrap modal instance (una vez)
    let modalRiesgo = null;

  function getModalRiesgo() {
    if (modalRiesgo) return modalRiesgo;

    if (typeof bootstrap === "undefined") {
      console.error("❌ Bootstrap JS no está cargado. No se puede inicializar el modal.");
      return null;
    }

    modalRiesgo = new bootstrap.Modal(modalElem, {
      backdrop: "static",
      keyboard: false
    });

    return modalRiesgo;
  }


  // 👇 este es EL caso actual donde se guardará el JSON
  let casoActual = null;

  /* ============================================================
     1) VALIDACIONES EN TIEMPO REAL (delegación global)
  ============================================================ */
  if (!document.body.dataset.rvInputBound) {
    document.addEventListener("input", function (e) {
      const el = e.target;
      if (!el || !el.classList) return;

      if (el.classList.contains("rv_dni")) {
        el.value = el.value.replace(/\D/g, "").slice(0, 8);
        if (el.value.length === 8) el.classList.remove("is-invalid");
        return;
      }

      if (el.classList.contains("rv_apellidos") || el.classList.contains("rv_nombres")) {
        el.value = el.value.toUpperCase();
        if (el.value.trim() !== "") el.classList.remove("is-invalid");
        return;
      }

      if (el.classList.contains("rv_dom_txt") || el.classList.contains("rv_act_txt") || el.classList.contains("rv_pre_txt")) {
        el.value = el.value.toUpperCase();
        if (el.value.trim() !== "") el.classList.remove("is-invalid");
        return;
      }
    });

    document.body.dataset.rvInputBound = "1";
  }

  /* ============================================================
     2) SI/NO por fila (no duplica listeners)
  ============================================================ */
  function configurarCamposSiNo(fila) {
    if (!fila) return;

    const domSi = fila.querySelector(".rv_dom_si");
    const domTxt = fila.querySelector(".rv_dom_txt");

    const actSi = fila.querySelector(".rv_act_si");
    const actTxt = fila.querySelector(".rv_act_txt");

    const preSi = fila.querySelector(".rv_pre_si");
    const preTxt = fila.querySelector(".rv_pre_txt");

    if (!domSi || !domTxt || !actSi || !actTxt || !preSi || !preTxt) return;

    function toggleCampo(select, campo, textoNo) {
      if (select.value === "Si") {
        campo.disabled = false;
        if ((campo.value || "").trim() === "" || campo.value === textoNo) campo.value = "";
        if ((campo.value || "").trim() === "") campo.classList.add("is-invalid");
      } else {
        campo.disabled = true;
        campo.value = textoNo;
        campo.classList.remove("is-invalid");
      }
    }

    // evitar duplicidad si vuelves a llamar la función
    if (!fila.dataset.siNoBound) {
      domSi.addEventListener("change", () => toggleCampo(domSi, domTxt, "NO TIENE DOMICILIO VINCULADO"));
      actSi.addEventListener("change", () => toggleCampo(actSi, actTxt, "NO TIENE ACTIVIDAD REGISTRADA"));
      preSi.addEventListener("change", () => toggleCampo(preSi, preTxt, "NO TIENE PREDIO VINCULADO"));
      fila.dataset.siNoBound = "1";
    }

    // init
    toggleCampo(domSi, domTxt, "NO TIENE DOMICILIO VINCULADO");
    toggleCampo(actSi, actTxt, "NO TIENE ACTIVIDAD REGISTRADA");
    toggleCampo(preSi, preTxt, "NO TIENE PREDIO VINCULADO");
  }

  /* ============================================================
     3) Agregar fila
  ============================================================ */
  function agregarFila(data = {}) {
    const clone = tplVinc.content.cloneNode(true);
    const fila = clone.querySelector(".fila-vinculado");
    if (!fila) return;

    fila.querySelector(".num-vinc").textContent =
      "V" + (contVinc.querySelectorAll(".fila-vinculado").length + 1);

    fila.querySelector(".rv_dni").value = data.dni || "";
    fila.querySelector(".rv_apellidos").value = data.apellidos || "";
    fila.querySelector(".rv_nombres").value = data.nombres || "";
    fila.querySelector(".rv_grado").value = data.grado_consanguinidad || "";

    fila.querySelector(".rv_dom_si").value = data.domicilio_si || "No";
    fila.querySelector(".rv_dom_txt").value = data.domicilio_texto || "";

    fila.querySelector(".rv_act_si").value = data.actividad_si || "No";
    fila.querySelector(".rv_act_txt").value = data.actividad_texto || "";

    fila.querySelector(".rv_pre_si").value = data.predio_si || "No";
    fila.querySelector(".rv_pre_txt").value = data.predio_texto || "";

    contVinc.appendChild(fila);
    configurarCamposSiNo(fila);
  }

  /* ============================================================
     4) Render de vinculados existentes
  ============================================================ */
  function renderVinculadosEnModal(vinculados) {
    contVinc.innerHTML = "";

    if (!Array.isArray(vinculados) || vinculados.length === 0) {
      agregarFila();
      return;
    }

    vinculados.forEach(v => agregarFila(v));
  }

  /* ============================================================
     5) VALIDACIÓN GLOBAL
  ============================================================ */
  function validarVinculados() {
    // DNIs duplicados
    const listaDni = [];
    let duplicado = null;

    contVinc.querySelectorAll(".fila-vinculado").forEach(f => {
      const dni = (f.querySelector(".rv_dni")?.value || "").trim();
      if (dni.length === 8) {
        if (listaDni.includes(dni)) duplicado = dni;
        listaDni.push(dni);
      }
    });

    if (duplicado) {
      (typeof customAlert === "function" ? customAlert : alert)(
        "⚠ El DNI " + duplicado + " está duplicado en la lista de vinculados."
      );
      contVinc.querySelectorAll(".fila-vinculado").forEach(f => {
        const elDni = f.querySelector(".rv_dni");
        if (elDni && elDni.value.trim() === duplicado) elDni.classList.add("is-invalid");
      });
      return false;
    }

    let ok = true;
    let firstError = null;

    contVinc.querySelectorAll(".fila-vinculado").forEach(f => {
      const dni = f.querySelector(".rv_dni");
      const ape = f.querySelector(".rv_apellidos");
      const nom = f.querySelector(".rv_nombres");
      const grado = f.querySelector(".rv_grado");

      const domSi = f.querySelector(".rv_dom_si");
      const domTx = f.querySelector(".rv_dom_txt");

      const actSi = f.querySelector(".rv_act_si");
      const actTx = f.querySelector(".rv_act_txt");

      const preSi = f.querySelector(".rv_pre_si");
      const preTx = f.querySelector(".rv_pre_txt");

      if (!dni || dni.value.length !== 8) { dni?.classList.add("is-invalid"); ok = false; if (!firstError) firstError = dni; }
      if (!ape || ape.value.trim() === "") { ape?.classList.add("is-invalid"); ok = false; if (!firstError) firstError = ape; }
      if (!nom || nom.value.trim() === "") { nom?.classList.add("is-invalid"); ok = false; if (!firstError) firstError = nom; }
      if (!grado || !grado.value) { grado?.classList.add("is-invalid"); ok = false; if (!firstError) firstError = grado; }

      if (domSi && domTx && domSi.value === "Si" && domTx.value.trim() === "") { domTx.classList.add("is-invalid"); ok = false; if (!firstError) firstError = domTx; }
      if (actSi && actTx && actSi.value === "Si" && actTx.value.trim() === "") { actTx.classList.add("is-invalid"); ok = false; if (!firstError) firstError = actTx; }
      if (preSi && preTx && preSi.value === "Si" && preTx.value.trim() === "") { preTx.classList.add("is-invalid"); ok = false; if (!firstError) firstError = preTx; }
    });

    if (!ok) {
      (typeof customAlert === "function" ? customAlert : alert)(
        "⚠ COMPLETE TODOS LOS CAMPOS DEL FORMULARIO DE RIESGO VINCULADO."
      );
      firstError?.focus?.();
    }

    return ok;
  }

  /* ============================================================
     6) FUNCIÓN GLOBAL para ABRIR MODAL (🔥 clave del fix)
     - Aquí se setea casoActual
  ============================================================ */
// Variables globales del módulo (arriba del archivo)
  window.abrirModalRiesgoVinculado = function (caso) {
    try {
      if (!caso) return;

      // 1) asegurar casoActual
      casoActual = caso;

      // 2) asegurar que existe el modal en el DOM
      const modalEl = document.getElementById("modalRiesgoVinculado");
      if (!modalEl) {
        console.error("❌ No existe #modalRiesgoVinculado en el HTML.");
        return;
      }

      // 3) inicializar Bootstrap Modal SOLO si aún no existe
      modalRiesgo = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl, {
        backdrop: "static",
        keyboard: false
      });

      // 4) asegurar contenedor y template
      const contVinc = document.getElementById("contenedorVinculados");
      const tplVinc  = document.getElementById("tpl-vinculado");
      if (!contVinc || !tplVinc) {
        console.error("❌ Falta #contenedorVinculados o #tpl-vinculado en el HTML.");
        return;
      }

      // 5) limpiar y renderizar filas desde el JSON oculto del caso
      contVinc.innerHTML = "";

      const hidden = casoActual.querySelector(".rv_json");
      let vinculados = [];
      try { vinculados = JSON.parse(hidden?.value || "[]"); } catch { vinculados = []; }

      // helper: agrega fila (debes tener esta función ya en el mismo archivo)
      if (!Array.isArray(vinculados) || vinculados.length === 0) {
        agregarFila(); // <-- usa tu función existente
      } else {
        vinculados.forEach(v => agregarFila(v));
      }

      // 6) mostrar modal
      modalRiesgo.show();

    } catch (err) {
      console.error("❌ Error en abrirModalRiesgoVinculado:", err);
    }
  };


  /* ============================================================
     7) Botón AGREGAR fila (no duplica)
  ============================================================ */
  if (btnAgregar && !btnAgregar.dataset.bound) {
    btnAgregar.addEventListener("click", () => agregarFila());
    btnAgregar.dataset.bound = "1";
  }

  /* ============================================================
     8) Eliminar fila (delegación, no duplica)
  ============================================================ */
  if (!document.body.dataset.rvDeleteBound) {
    document.addEventListener("click", (e) => {
      if (!e.target.classList.contains("btnEliminarVinc")) return;

      const fila = e.target.closest(".fila-vinculado");
      if (!fila) return;
      fila.remove();

      // Renumerar
      contVinc.querySelectorAll(".fila-vinculado").forEach((f, i) => {
        const n = f.querySelector(".num-vinc");
        if (n) n.textContent = "V" + (i + 1);
      });
    });

    document.body.dataset.rvDeleteBound = "1";
  }

  /* ============================================================
     9) Guardar (no duplica) → guarda en .rv_json del casoActual
  ============================================================ */
  if (!btnGuardar.dataset.saveBound) {
    btnGuardar.addEventListener("click", () => {
      if (!casoActual) {
        console.warn("⚠ casoActual es null. No sé a qué caso guardar.");
        return;
      }

      if (!validarVinculados()) return;

      const vinculados = [];
      contVinc.querySelectorAll(".fila-vinculado").forEach((f) => {
        vinculados.push({
          dni: (f.querySelector(".rv_dni")?.value || "").trim(),
          apellidos: (f.querySelector(".rv_apellidos")?.value || "").trim(),
          nombres: (f.querySelector(".rv_nombres")?.value || "").trim(),
          grado_consanguinidad: f.querySelector(".rv_grado")?.value || "",

          domicilio_si: f.querySelector(".rv_dom_si")?.value || "No",
          domicilio_texto: (f.querySelector(".rv_dom_txt")?.value || "").trim(),

          actividad_si: f.querySelector(".rv_act_si")?.value || "No",
          actividad_texto: (f.querySelector(".rv_act_txt")?.value || "").trim(),

          predio_si: f.querySelector(".rv_pre_si")?.value || "No",
          predio_texto: (f.querySelector(".rv_pre_txt")?.value || "").trim(),
        });
      });

      const hidden = casoActual.querySelector(".rv_json");
      if (hidden) hidden.value = JSON.stringify(vinculados);

      console.log("✅ Vinculados guardados en rv_json:", hidden?.value);
      modalRiesgo.hide();
    });

    btnGuardar.dataset.saveBound = "1";
  }

})();