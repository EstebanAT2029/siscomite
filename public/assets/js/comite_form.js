/* ============================================================
   SISCOMITE — REGISTRO DE COMITÉ
============================================================ */

document.addEventListener("DOMContentLoaded", () => {
    // Elementos principales
    // ✅ Al cargar el formulario → hora del sistema
    actualizarHoraSistema(true);

    const selAgencia   = document.getElementById("agencia");
    const selOf1       = document.getElementById("oficial1");
    const selOf2       = document.getElementById("oficial2");
    const selJefe      = document.getElementById("jefe_ag");

    const btnEmpezar = document.getElementById("btnEmpezar");
    if (btnEmpezar) {
        btnEmpezar.addEventListener("click", () => {
            // 🔒 No forzar, solo asegurar que exista
            actualizarHoraSistema(false);
        });
    }

    const btnAñadir    = document.getElementById("btnAñadir");
    const btnFinalizar = document.getElementById("btnFinalizar");

    const contCasos     = document.getElementById("contenedor-casos");
    const plantillaCaso = document.getElementById("plantilla-caso");

    if (!selAgencia || !selOf1 || !selOf2 || !selJefe || !btnAñadir) {
        console.warn("comite_form.js: No está en formulario, módulo detenido.");
        return;
    }

    let oficialesGlobal = [];
    let jefesGlobal     = [];
    let criteriosGlobal = []; // ✅ NUEVO
    let numCaso = 0;

    /* ============================================================
       0. Cargar criterios (para combos por caso)
    ============================================================= */
    fetch("index.php?url=api/criterios")
        .then(res => res.json())
        .then(data => {
            criteriosGlobal = data || [];
        })
        .catch(err => {
            console.error("Error cargando criterios:", err);
            criteriosGlobal = [];
        });

    /* ============================================================
       1. Cargar agencias según zona del usuario
    ============================================================= */
    fetch("index.php?url=api/agencias")
        .then(res => res.json())
        .then(data => {
            llenarCombo(selAgencia, data, "id", "nombre_agencia");
        })
        .catch(err => console.error("Error cargando agencias:", err));

    /* ============================================================
       2. Cambia agencia → cargar oficiales y jefes
    ============================================================= */
    selAgencia.addEventListener("change", () => {
        const id = selAgencia.value;

        limpiarCombo(selOf1);
        limpiarCombo(selOf2);
        limpiarCombo(selJefe);

        if (!id) return;

        cargarOficiales(id);
        cargarJefes(id);
    });

    function cargarOficiales(idAgencia) {

        fetch(`index.php?url=api/oficiales&agencia_id=${idAgencia}`)
            .then(res => res.json())
            .then(data => {
                oficialesGlobal = data || [];

                llenarCombo(selOf1, oficialesGlobal, "id", "nombre");
                llenarCombo(selOf2, oficialesGlobal, "id", "nombre");

                actualizarComboProponentes();
            })
            .catch(err => console.error("Error oficiales:", err));
    }

    function cargarJefes(idAgencia) {
        fetch(`index.php?url=api/jefes&agencia_id=${idAgencia}`)
            .then(res => res.json())
            .then(data => {
                jefesGlobal = data || [];
                llenarCombo(selJefe, jefesGlobal, "id", "nombre");
            })
            .catch(err => console.error("Error jefes:", err));
    }

    /* ============================================================
       UTILS
    ============================================================= */
    function llenarCombo(select, data, idField, textField) {
        limpiarCombo(select);
        (data || []).forEach(item => {
            select.innerHTML += `<option value="${item[idField]}">${item[textField]}</option>`;
        });
    }

    function limpiarCombo(select) {
        select.innerHTML = `<option value="">Seleccione</option>`;
    }

    // ✅ NUEVO: llenar combo criterios (value=id, texto=codigo)
    function llenarComboCriterios(select) {
        if (!select) return;
        select.innerHTML = `<option value="">Seleccione</option>`;
        (criteriosGlobal || []).forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.codigo}</option>`;
        });
    }

    /* ============================================================
       Validación oficial1 != oficial2
    ============================================================= */
    function validarParticipantes() {
        if (selOf1.value && selOf1.value === selOf2.value) {
            customAlert("⚠ No puede repetir el mismo oficial como participante 1 y 2.");
            selOf2.value = "";
        }
        actualizarComboProponentes();
    }

    selOf1.addEventListener("change", validarParticipantes);
    selOf2.addEventListener("change", validarParticipantes);

    function actualizarComboProponentes() {

        const casos = document.querySelectorAll(".caso-item");

        // Caso único → filtrar
        if (casos.length === 1) {

            const usados = new Set([selOf1.value, selOf2.value]);

            const filtrados = oficialesGlobal.filter(o => !usados.has(String(o.id)));

            const select = casos[0].querySelector(".oficial_prop");

            // ⚠️ SOLO si aún no tiene valor
            if (!select.value) {
                llenarCombo(select, filtrados, "id", "nombre");
            }

            return;
        }

        // Dos o más casos → liberar SIN resetear
        casos.forEach(caso => {

            const select = caso.querySelector(".oficial_prop");

            // 🔒 Si ya eligieron algo → NO TOCAR
            if (select.value) return;

            // 🔓 Solo llenar si está vacío
            llenarCombo(select, oficialesGlobal, "id", "nombre");
        });
    }

    /* ============================================================
       3. Empezar comité
    ============================================================= */
    btnEmpezar.addEventListener("click", () => {

        if (!selAgencia.value || !selOf1.value || !selOf2.value || !selJefe.value) {
            customAlert("⚠ Complete todos los campos del encabezado.", "Validación");
            return;
        }

        selAgencia.disabled = true;
        selOf1.disabled     = true;
        selOf2.disabled     = true;
        selJefe.disabled    = true;

        btnEmpezar.disabled   = true;
        btnAñadir.disabled    = false;
        btnFinalizar.disabled = false;

        actualizarComboProponentes();
    });

    /* ============================================================
       4. Añadir caso
    ============================================================ */
    btnAñadir.addEventListener("click", () => {

        numCaso++;

        const clone   = plantillaCaso.content.cloneNode(true);
        const divCaso = clone.querySelector(".caso-item");

        divCaso.querySelector(".titulo-caso").textContent = `Caso ${numCaso}`;

        // ✅ NUEVO: llenar criterios en este caso (antes de decisión)
        const selCriterio = divCaso.querySelector(".criterio");
        llenarComboCriterios(selCriterio);

        contCasos.appendChild(clone);

        // 🔥 MUY IMPORTANTE:
        // recalcula los oficiales proponentes según
        // la cantidad total de casos existentes
        actualizarComboProponentes();
    });

    /* ============================================================
       🔒 VALIDACIÓN FUERTE: si algo falta, NO mostrar modal resumen
       (esto se ejecuta ANTES del resumen)
    ============================================================= */
    function validarTodoAntesDeResumen() {

        // Encabezado
        if (!selAgencia.value || !selOf1.value || !selOf2.value || !selJefe.value) {
            customAlert("⚠ Complete todos los campos del encabezado.", "Validación");
            return false;
        }

        const casos = document.querySelectorAll(".caso-item");
        if (casos.length === 0) {
            customAlert("⚠ Debe añadir al menos un caso.", "Validación");
            return false;
        }

        let hayError = false;

        const marcar = (el) => {
            if (!el) return;
            el.classList.add("is-invalid");
            hayError = true;
        };
        const limpiar = (el) => {
            if (!el) return;
            el.classList.remove("is-invalid");
        };

        casos.forEach((caso) => {

            const dni       = caso.querySelector(".dni");
            const cadena    = caso.querySelector(".cadena"); // opcional (no lo marco)
            const nombres   = caso.querySelector(".nombres");
            const monto     = caso.querySelector(".monto");
            const tipoCli   = caso.querySelector(".tipo_cli");
            const tipoCred  = caso.querySelector(".tipo_credito"); // opcional (no lo marco)
            const ofProp    = caso.querySelector(".oficial_prop");
            const criterio  = caso.querySelector(".criterio");
            const decision  = caso.querySelector(".decision");

            // limpiar marcas previas
            [dni, nombres, monto, tipoCli, ofProp, criterio, decision].forEach(limpiar);

            // obligatorios mínimos
            if (!dni?.value?.trim()) marcar(dni);
            if (!nombres?.value?.trim()) marcar(nombres);

            // monto > 0
            const m = parseFloat((monto?.value || "").toString().replace(/,/g, ""));
            if (!monto?.value?.trim() || isNaN(m) || m <= 0) marcar(monto);

            if (!tipoCli?.value?.trim()) marcar(tipoCli);
            if (!ofProp?.value?.trim()) marcar(ofProp);

            // ✅ criterio obligatorio
            if (!criterio?.value?.trim()) marcar(criterio);

            // decisión (siempre trae valor, pero por seguridad)
            if (!decision?.value?.trim()) marcar(decision);
        });

        if (hayError) {
            customAlert("⚠ Hay campos obligatorios pendientes en uno o más casos.", "Validación");
            return false;
        }

        return true;
    }

    /* ============================================================
       5. Finalizar Comité — LLAMADO DESDE validacion.js
       ✅ Ahora: SOLO si valida todo → muestra modal resumen
    ============================================================= */
    window.finalizarComite = function () {
        // 🔥 OJO: aquí ya viene TODO validado desde validacion.js
        const ok = construirResumenAntesDeFinalizar();
        if (!ok) return;

        const modalEl = document.getElementById("modalResumenComite");
        if (!modalEl) {
            enviarComite();
            return;
        }

        // 🔒 No cerrar fuera ni con ESC (forzado)
        const modal = new bootstrap.Modal(modalEl, { backdrop: "static", keyboard: false });
        modal.show();
    };


    // botón continuar dentro del modal
    document.getElementById("btnContinuarFinalizacion")?.addEventListener("click", () => {
        enviarComite();
    });
    // ✅ Botón Regresar: cierra modal correctamente y libera la pantalla
    document.getElementById("btnRegresarResumen")?.addEventListener("click", () => {

        const modalEl = document.getElementById("modalResumenComite");
        if (!modalEl) return;

        // Cerrar con API Bootstrap (evita que quede el backdrop bloqueando)
        const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        inst.hide();

        // 🔧 Por seguridad: remover backdrop si quedara colgado (caso raro)
        setTimeout(() => {
            document.querySelectorAll(".modal-backdrop").forEach(b => b.remove());
            document.body.classList.remove("modal-open");
            document.body.style.removeProperty("padding-right");
        }, 300);

        // ✅ Opcional: enfocar el primer campo del último caso
        setTimeout(() => {
            const ultimoCaso = document.querySelector(".caso-item:last-child");
            const foco = ultimoCaso?.querySelector(".dni") || document.getElementById("agencia");
            foco?.focus();
        }, 350);
    });


    /* ============================================================
       Enviar comité (antes estaba dentro de finalizarComite)
    ============================================================= */
    function enviarComite() {

        const payload = armarJSON();
        console.log("➡ Enviando JSON:", payload);

        fetch("index.php?url=comite/store", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(r => r.text())
        .then(t => {
            console.log("RAW RESPONSE:", t);

            let resp = JSON.parse(t);

            if (!resp.success) {
                customAlert("❌ Error al guardar comité: " + resp.error);
                return;
            }

            window.ID_COMITE  = resp.id_comite;
            window.ID_DETALLE = resp.id_detalle;

            // cerrar modal resumen si está abierto
            const modalEl = document.getElementById("modalResumenComite");
            if (modalEl) {
                const inst = bootstrap.Modal.getInstance(modalEl);
                if (inst) inst.hide();
            }

            const modal = new bootstrap.Modal(
                document.getElementById("modalTipoComite"),
                { backdrop: "static", keyboard: false }
            );
            modal.show();
        })
        .catch(err => {
            console.error("Error conectando:", err);
            customAlert("❌ Error de conexión.");
        });
    }

    /* ============================================================
       Construye JSON
       ✅ Agrega id_criterio por caso
    ============================================================= */
    function armarJSON() {

        const casos = [];

        document.querySelectorAll(".caso-item").forEach(caso => {

            let vinculados = [];
            try { vinculados = JSON.parse(caso.querySelector(".rv_json").value); } catch {}

            casos.push({
                dni:          caso.querySelector(".dni").value,
                cadena:       caso.querySelector(".cadena").value,
                nombres:      caso.querySelector(".nombres").value,
                monto: parseFloat(
                    (caso.querySelector(".monto").value || "").replace(/,/g, "")
                ) || 0,
                tipo_cli:     caso.querySelector(".tipo_cli").value,
                tipo_credito: caso.querySelector(".tipo_credito").value,
                oficial_prop: caso.querySelector(".oficial_prop").value,

                // ✅ NUEVO
                id_criterio:  caso.querySelector(".criterio")?.value || "",

                decision:     caso.querySelector(".decision").value,
                comentarios:  caso.querySelector(".comentarios").value,
                vinculados:   vinculados
            });
        });

        return {
            fecha:        document.getElementById("fecha").value,
            hora:         document.getElementById("hora").value,
            agencia:      selAgencia.value,
            oficial1:     selOf1.value,
            oficial2:     selOf2.value,
            jefe_agencia: selJefe.value,
            casos:        casos
        };
    }

    /* ============================================================
       ✅ Resumen modal (similar a tu 2da imagen)
       Nota: aquí ya no decide si abrir modal; eso lo decide validarTodoAntesDeResumen()
    ============================================================= */
    function construirResumenAntesDeFinalizar() {

        const tbody = document.getElementById("resumenBody");
        const alertBox = document.getElementById("resumenErrores");
        const btnContinuar = document.getElementById("btnContinuarFinalizacion");

        // si no existe modal, no bloqueamos
        if (!tbody) return true;

        tbody.innerHTML = "";
        alertBox?.classList.add("d-none");
        if (btnContinuar) btnContinuar.disabled = false;

        const casos = document.querySelectorAll(".caso-item");
        if (casos.length === 0) {
            customAlert("⚠ Debe añadir al menos un caso.", "Validación");
            return false;
        }

        // Como ya validamos antes, esto casi nunca se activa,
        // pero lo dejamos por seguridad.
        let hayErrores = false;

        casos.forEach((caso, idx) => {
            const dni = (caso.querySelector(".dni")?.value || "").trim();
            const cliente = (caso.querySelector(".nombres")?.value || "").trim();
            const monto = (caso.querySelector(".monto")?.value || "").trim();
            const tipoCli = (caso.querySelector(".tipo_cli")?.value || "").trim();

            const selCrit = caso.querySelector(".criterio");
            const idCrit = selCrit?.value || "";
            const critTxt = selCrit?.selectedOptions?.[0]?.textContent?.trim() || "";

            const selDec = caso.querySelector(".decision");
            const decTxt = (selDec?.value || "").trim();

            if (!idCrit || !decTxt) hayErrores = true;

            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td><b>${idx + 1}</b></td>
              <td>${dni || '<span class="text-muted">—</span>'}</td>
              <td>${cliente || '<span class="text-muted">—</span>'}</td>
              <td class="text-end">${fmtMonto(monto)}</td>
              <td>${tipoCli || '<span class="text-muted">—</span>'}</td>
              <td>${badgeCriterio(critTxt || '-')}</td>
              <td>${badgeDecision(decTxt || '-')}</td>
            `;
            tbody.appendChild(tr);
        });

        if (hayErrores) {
            alertBox?.classList.remove("d-none");
            if (btnContinuar) btnContinuar.disabled = true;

            // 🔒 si algo fallara, no permitimos seguir
            return false;
        }

        return true;
    }

    function badgeCriterio(text) {
        return `<span class="badge bg-primary">${escapeHtml(text)}</span>`;
    }

    function badgeDecision(text) {
        const t = (text || "").toLowerCase();
        let cls = "bg-secondary";
        if (t === "aprobado") cls = "bg-success";
        if (t === "observado") cls = "bg-warning text-dark";
        if (t === "denegado") cls = "bg-danger";
        return `<span class="badge ${cls}">${escapeHtml(text)}</span>`;
    }

    function fmtMonto(val) {
        const n = parseFloat((val || "").toString().replace(/,/g, ""));
        if (isNaN(n)) return "-";
        return n.toLocaleString("es-PE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(str) {
        return (str || "").replace(/[&<>"']/g, (m) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        }[m]));
    }

    function actualizarHoraSistema(force = false) {
        const inputHora = document.getElementById("hora");
        if (!inputHora) return;

        // ❗ Solo setear si está vacío o si se fuerza
        if (inputHora.value && !force) return;

        const ahora = new Date();
        const hh = String(ahora.getHours()).padStart(2, "0");
        const mm = String(ahora.getMinutes()).padStart(2, "0");

        inputHora.value = `${hh}:${mm}`;
    }

});


