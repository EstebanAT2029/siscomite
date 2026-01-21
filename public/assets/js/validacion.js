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
   ✅ VALIDACIÓN EN SELECT (change) — NUEVO PARA CRITERIO
============================================================ */
document.addEventListener("change", function (e) {
    const el = e.target;

    if (el.classList.contains("criterio") || el.classList.contains("decision") || el.classList.contains("oficial_prop")) {
        if (el.value.trim() !== "") el.classList.remove("is-invalid");
    }
});


/* ============================================================
   VALIDACIÓN GLOBAL AL FINALIZAR
============================================================ */
function validarCamposObligatorios() {

    let ok = true;
    let primerError = null;

    // Encabezado
    ["fecha", "hora", "agencia", "oficial1", "oficial2", "jefe_ag"].forEach(id => {
        let campo = document.getElementById(id);
        campo.classList.remove("is-invalid");
        if (!campo.value.trim()) {
            campo.classList.add("is-invalid");
            if (!primerError) primerError = campo;
            ok = false;
        }
    });

    // Casos
    document.querySelectorAll(".caso-item").forEach(caso => {

        let dni = caso.querySelector(".dni");
        let cadena = caso.querySelector(".cadena");
        let nombres = caso.querySelector(".nombres");
        let comentarios = caso.querySelector(".comentarios");
        let monto = caso.querySelector(".monto");
        let tipo_cli = caso.querySelector(".tipo_cli");
        let tipo_credito = caso.querySelector(".tipo_credito");
        let oficial_prop = caso.querySelector(".oficial_prop");

        // ✅ NUEVO
        let criterio = caso.querySelector(".criterio");

        let decision = caso.querySelector(".decision");

        const lista = [
            dni, cadena, nombres, monto, tipo_cli, tipo_credito,
            oficial_prop, criterio, decision
        ];

        lista.forEach(c => {
            c.classList.remove("is-invalid");
            if (!c.value.trim()) {
                c.classList.add("is-invalid");
                if (!primerError) primerError = c;
                ok = false;
            }
        });

        if (dni.value.length !== 8) {
            dni.classList.add("is-invalid");
            ok = false;
        }

        // OJO: tu regla actual es <= 1 (la mantengo igual)
        if (parseFloat((monto.value || "").replace(/,/g, "")) <= 1) {
            monto.classList.add("is-invalid");
            ok = false;
        }

    });

    if (!ok) {
        alert("⚠ POR FAVOR COMPLETE CORRECTAMENTE TODOS LOS CAMPOS ANTES DE FINALIZAR.");
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
        let continuar = confirm("⚠ No ingresó comentarios. ¿Desea continuar sin observaciones?");
        if (!continuar) {
            comentarios[0].classList.add("is-invalid");
            comentarios[0].focus();
            return;
        }
    }

    // TODO OK → REGISTRAR COMITÉ
    window.finalizarComite();
}


/* ============================================================
   BOTÓN FINALIZAR — ES LA PUERTA DE CONTROL
============================================================ */
document.addEventListener("DOMContentLoaded", () => {

    const btnFinalizar = document.getElementById("btnFinalizar");

    btnFinalizar.addEventListener("click", (e) => {

        e.preventDefault();

        if (!validarCamposObligatorios()) return;

        validarObservacionesYConfirmar();
    });

});