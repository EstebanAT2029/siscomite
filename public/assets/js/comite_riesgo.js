/* ============================================================
   RIESGO VINCULADO — Modal y múltiples filas
============================================================ */

document.addEventListener("DOMContentLoaded", () => {

    console.log("✔ comite_riesgo.js cargado");

    /* ------------------------------------------------------------
       Validar existencia del modal
    ------------------------------------------------------------ */
    const modalElem = document.getElementById("modalRiesgoVinculado");
    if (!modalElem) {
        console.warn("comite_riesgo.js: No existe modalRiesgoVinculado. Se omite módulo de riesgo.");
        return;
    }

    let modalRiesgo = new bootstrap.Modal(modalElem);
    let casoActual = null;

    const contVinc = document.getElementById("contenedorVinculados");
    const tplVinc  = document.getElementById("tpl-vinculado");

    if (!contVinc || !tplVinc) {
        console.warn("comite_riesgo.js: No existen contVinc o tplVinc. Se omite módulo de riesgo.");
        return;
    }

    /* ------------------------------------------------------------
       Cuando seleccionan "Riesgo Vinculado: SI"
    ------------------------------------------------------------ */
    document.addEventListener("change", e => {
        if (!e.target.classList.contains("sel-riesgo-c8")) return;

        const caso = e.target.closest(".caso-item");
        const hiddenJson = caso.querySelector(".rv_json");

        if (e.target.value === "Si") {

            casoActual = caso;
            contVinc.innerHTML = "";

            let vinculados = [];
            if (hiddenJson.value) {
                try { vinculados = JSON.parse(hiddenJson.value); } catch {}
            }

            if (!vinculados.length) {
                agregarFila();
            } else {
                vinculados.forEach(v => agregarFila(v));
            }

            modalRiesgo.show();
        } else {
            hiddenJson.value = "[]";
        }
    });

    /* ------------------------------------------------------------
       Agregar fila — proteger botón
    ------------------------------------------------------------ */
    const btnAgregarVinc = document.getElementById("btnAgregarVinc");
    if (btnAgregarVinc) {
        btnAgregarVinc.addEventListener("click", () => {
            agregarFila();
        });
    } else {
        console.warn("comite_riesgo.js: No existe btnAgregarVinc");
    }

    function agregarFila(datos = {}) {
        const clone = tplVinc.content.cloneNode(true);
        const fila = clone.querySelector(".fila-vinculado");

        fila.querySelector(".num-vinc").textContent =
            contVinc.querySelectorAll(".fila-vinculado").length + 1;

        fila.querySelector(".rv_dni").value       = datos.dni || "";
        fila.querySelector(".rv_apellidos").value = datos.apellidos || "";
        fila.querySelector(".rv_nombres").value   = datos.nombres || "";
        fila.querySelector(".rv_grado").value     = datos.grado_consanguinidad || "";

        fila.querySelector(".rv_dom_si").value  = datos.domicilio_si || "No";
        fila.querySelector(".rv_dom_txt").value = datos.domicilio_texto || "";

        fila.querySelector(".rv_act_si").value  = datos.actividad_si || "No";
        fila.querySelector(".rv_act_txt").value = datos.actividad_texto || "";

        fila.querySelector(".rv_pre_si").value  = datos.predio_si || "No";
        fila.querySelector(".rv_pre_txt").value = datos.predio_texto || "";

        contVinc.appendChild(fila);
    }

    /* ------------------------------------------------------------
       Eliminar fila
    ------------------------------------------------------------ */
    document.addEventListener("click", e => {
        if (!e.target.classList.contains("btnEliminarVinc")) return;

        const fila = e.target.closest(".fila-vinculado");
        fila.remove();

        contVinc.querySelectorAll(".fila-vinculado").forEach(
            (f, i) => f.querySelector(".num-vinc").textContent = i + 1
        );
    });

    /* ------------------------------------------------------------
       Guardar en hidden JSON — proteger botón
    ------------------------------------------------------------ */
    const btnGuardarRV = document.getElementById("btnGuardarRV");
    if (btnGuardarRV) {
        btnGuardarRV.addEventListener("click", () => {

            if (!casoActual) {
                console.warn("comite_riesgo.js: No hay casoActual definido.");
                return;
            }

            const vinculados = [];

            contVinc.querySelectorAll(".fila-vinculado").forEach(f => {
                const obj = {
                    dni: f.querySelector(".rv_dni").value.trim(),
                    apellidos: f.querySelector(".rv_apellidos").value.trim(),
                    nombres: f.querySelector(".rv_nombres").value.trim(),
                    grado_consanguinidad: f.querySelector(".rv_grado").value,

                    domicilio_si: f.querySelector(".rv_dom_si").value,
                    domicilio_texto: f.querySelector(".rv_dom_txt").value.trim(),

                    actividad_si: f.querySelector(".rv_act_si").value,
                    actividad_texto: f.querySelector(".rv_act_txt").value.trim(),

                    predio_si: f.querySelector(".rv_pre_si").value,
                    predio_texto: f.querySelector(".rv_pre_txt").value.trim()
                };

                if (
                    obj.dni ||
                    obj.apellidos ||
                    obj.nombres ||
                    obj.domicilio_texto ||
                    obj.actividad_texto ||
                    obj.predio_texto
                ) {
                    vinculados.push(obj);
                }
            });

            casoActual.querySelector(".rv_json").value = JSON.stringify(vinculados);

            modalRiesgo.hide();
        });
    } else {
        console.warn("comite_riesgo.js: No existe btnGuardarRV");
    }
});