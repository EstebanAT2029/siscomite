/* ============================================================
   VALIDACIONES EN LÍNEA (REAL-TIME)
============================================================ */
document.addEventListener("input", function (e) {

    let el = e.target;

    // DNI
    if (el.classList.contains("dni")) {
        el.value = el.value.replace(/\D/g, "").slice(0, 8);
        if (el.value.length === 8) el.classList.remove("is-invalid");
    }

    // CADENA
    if (el.classList.contains("cadena")) {
        el.value = el.value.replace(/\D/g, "");
        if (el.value.trim() !== "") el.classList.remove("is-invalid");
    }

    // MONTO
    if (el.classList.contains("monto")) {

        const start = el.selectionStart;

        let valor = el.value.replace(/[^0-9.,]/g, "");

        const partes = valor.split(".");
        if (partes.length > 2) {
            valor = partes.shift() + "." + partes.join("");
        }

        el.value = valor;
        el.setSelectionRange(start, start);

        const numero = parseFloat(valor.replace(/,/g, ""));
        if (!isNaN(numero) && numero > 0) {
            el.classList.remove("is-invalid");
        }
    }

    // TEXTOS MAYÚSCULA
    if (
        el.classList.contains("nombres") ||
        el.classList.contains("tipo_cli") ||
        el.classList.contains("tipo_credito")
    ) {
        let p = el.selectionStart;
        el.value = el.value.toUpperCase();
        el.setSelectionRange(p, p);
        if (el.value.trim() !== "") el.classList.remove("is-invalid");
    }

});

/* ============================================================
   VALIDACIÓN EN SELECT (change)
============================================================ */
document.addEventListener("change", function (e) {
    const el = e.target;

    if (
        el.classList.contains("criterio") ||
        el.classList.contains("decision") ||
        el.classList.contains("oficial_prop")
    ) {
        if (esValorSelectValido(el.value)) el.classList.remove("is-invalid");
    }
});

/* ============================================================
   UTIL: value válido para selects
============================================================ */
function esValorSelectValido(val) {
    const v = String(val ?? "").trim();
    if (!v) return false;
    const invalidos = ["seleccione", "0", "null", "undefined", "-"];
    return !invalidos.includes(v.toLowerCase());
}

/* ============================================================
   VALIDACIÓN GLOBAL AL FINALIZAR
============================================================ */
function validarCamposObligatorios(silent = false) {

    let ok = true;
    let primerError = null;

    // Encabezado
    ["fecha", "hora", "agencia", "oficial1", "oficial2", "jefe_ag"].forEach(id => {
        let campo = document.getElementById(id);

        if (!campo) {
            ok = false;
            return;
        }

        campo.classList.remove("is-invalid");

        if (!String(campo.value ?? "").trim()) {
            campo.classList.add("is-invalid");
            if (!primerError) primerError = campo;
            ok = false;
        }
    });

    const casos = document.querySelectorAll(".caso-item");
    if (casos.length === 0) {
        alert("Debe añadir al menos un caso antes de finalizar.");
        return false;
    }

    // Casos
    casos.forEach(caso => {

        const dni          = caso.querySelector(".dni");
        const cadena       = caso.querySelector(".cadena");
        const nombres      = caso.querySelector(".nombres");
        const monto        = caso.querySelector(".monto");
        const tipo_cli     = caso.querySelector(".tipo_cli");
        const tipo_credito = caso.querySelector(".tipo_credito");
        const oficial_prop = caso.querySelector(".oficial_prop");
        const criterio     = caso.querySelector(".criterio");   // ✅ CRITERIO
        const decision     = caso.querySelector(".decision");

        let criterioObs = caso.querySelector(".criterio_observado");
        let criterioDen = caso.querySelector(".criterio_denegado");

            // VALIDACIÓN OBSERVADO
            if (decision.value === "Observado") {
                if (!criterioObs.value.trim()) {
                    criterioObs.classList.add("is-invalid");
                    ok = false;
                }
            }

            // VALIDACIÓN DENEGADO
            if (decision.value === "Denegado") {
                if (!criterioDen.value.trim()) {
                    criterioDen.classList.add("is-invalid");
                    ok = false;
                }
            }

        // ✅ Grupo obligatorio (incluye criterio)
        const obligatorios = [dni, cadena, nombres, monto, tipo_cli, tipo_credito, oficial_prop, criterio, decision];

        obligatorios.forEach(c => {

            if (!c) { // si no existe el control, es error
                ok = false;
                return;
            }

            c.classList.remove("is-invalid");

            // Validación especial para selects
            if (c.classList.contains("criterio") || c.classList.contains("oficial_prop") || c.classList.contains("decision")) {
                if (!esValorSelectValido(c.value)) {
                    c.classList.add("is-invalid");
                    if (!primerError) primerError = c;
                    ok = false;
                }
                return;
            }

            // Inputs normales
            if (!String(c.value ?? "").trim()) {
                c.classList.add("is-invalid");
                if (!primerError) primerError = c;
                ok = false;
            }
        });

        // DNI estricto
        if (dni && dni.value.length !== 8) {
            dni.classList.add("is-invalid");
            if (!primerError) primerError = dni;
            ok = false;
        }

        // Monto estricto
        if (monto) {
            const m = parseFloat(String(monto.value ?? "").replace(/,/g, ""));
            if (isNaN(m) || m <= 0) {
                monto.classList.add("is-invalid");
                if (!primerError) primerError = monto;
                ok = false;
            }
        }

    });

    if (!ok) {
        if (!silent) {
            alert("⚠ POR FAVOR COMPLETE CORRECTAMENTE TODOS LOS CAMPOS ANTES DE FINALIZAR.");
        }
        primerError?.focus();
    }

    return ok;
}

/* ============================================================
   VALIDACIÓN DE OBSERVACIONES + CONFIRMACIÓN
============================================================ */
function validarObservacionesYConfirmar() {

    const comentarios = document.querySelectorAll(".comentarios");
    let hay = [...comentarios].some(c => c.value.trim() !== "");

    if (!hay) {
        let continuar = confirm("No ingresó comentarios. ¿Desea continuar sin observaciones?");
        if (!continuar) {
            comentarios[0]?.classList.add("is-invalid");
            comentarios[0]?.focus();
            return;
        }
    }

    // Si falta algo -> NO abrir modal
    if (!validarCamposObligatorios()) return;

    // TODO OK → mostrar resumen / finalizar
    window.finalizarComite();
}

/* ============================================================
   BOTÓN FINALIZAR — ES LA PUERTA DE CONTROL
============================================================ */
document.addEventListener("DOMContentLoaded", () => {

    const btnFinalizar = document.getElementById("btnFinalizar");
    if (!btnFinalizar) return;

    btnFinalizar.addEventListener("click", (e) => {

        e.preventDefault();

        // Si falta algo -> NO abrir modal
        if (!validarCamposObligatorios()) return;

        validarObservacionesYConfirmar();
    });

});