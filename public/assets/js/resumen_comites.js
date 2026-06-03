document.addEventListener("DOMContentLoaded", () => {

    const cboZona = document.getElementById("zona");
    const cboAgencia = document.getElementById("agencia");
    const cboOficial = document.getElementById("oficial");
    const btnConsultar = document.getElementById("btnConsultar");
    const btnExportar = document.getElementById("btnExportar");
    const btnCancelar = document.getElementById("btnCancelar");

    cargarZonas();

    if (cboOficial) {
        cargarOficiales();
    }

    /* =========================================
       ZONAS
    ========================================= */
    async function cargarZonas() {

        try {

            const r = await fetch(
                "index.php?url=api/zonas-usuario"
            );

            const data = await r.json();

            data.forEach(z => {

                cboZona.innerHTML += `
                    <option value="${z.id}">
                        ${z.nombre}
                    </option>
                `;
            });

        } catch (e) {

            console.error("Error cargando zonas:", e);

        }
    }

    /* =========================================
       OFICIALES (USUARIOS)
    ========================================= */
    async function cargarOficiales() {

        try {

            const r = await fetch(
                "index.php?url=api/oficiales-zona"
            );

            const data = await r.json();

            data.forEach(o => {

                cboOficial.innerHTML += `
                    <option value="${o.id}">
                        ${o.nombre}
                    </option>
                `;
            });

        } catch (e) {

            console.error("Error cargando usuarios:", e);

        }
    }

    /* =========================================
       AGENCIAS POR ZONA
    ========================================= */
    cboZona.addEventListener("change", async () => {

        cboAgencia.innerHTML =
            '<option value="">Todas</option>';

        let zonas = cboZona.value;

        if (!zonas) return;

        try {

            const r = await fetch(
                `index.php?url=api/agencias-zona&zonas=${zonas}`
            );

            const data = await r.json();

            data.forEach(a => {

                cboAgencia.innerHTML += `
                    <option value="${a.id}">
                        ${a.nombre_agencia}
                    </option>
                `;
            });

        } catch (e) {

            console.error("Error cargando agencias:", e);

        }

    });

    /* =========================================
       OBTENER ZONAS
    ========================================= */
    function obtenerZonas() {

        // Todas las zonas del usuario
        if (!cboZona.value) {

            return Array.from(cboZona.options)
                .filter(o => o.value !== "")
                .map(o => parseInt(o.value));
        }

        // Una zona específica
        return [parseInt(cboZona.value)];
    }

    /* =========================================
       CONSULTAR
    ========================================= */
    if (btnConsultar) {

        btnConsultar.addEventListener(
            "click",
            consultarResumen
        );

    }
    btnCancelar.addEventListener("click", () => {

        document.getElementById(
            "contenedorTabla"
        ).innerHTML = "";

        btnConsultar.disabled = false;

        btnExportar.disabled = true;
    });

    async function consultarResumen() {

        const semanas = [

            {
                inicio: document.getElementById("s1i").value,
                fin: document.getElementById("s1f").value
            },

            {
                inicio: document.getElementById("s2i").value,
                fin: document.getElementById("s2f").value
            },

            {
                inicio: document.getElementById("s3i").value,
                fin: document.getElementById("s3f").value
            },

            {
                inicio: document.getElementById("s4i").value,
                fin: document.getElementById("s4f").value
            }

        ];
 
        /* ==========================================
        VALIDAR FECHAS
        ========================================== */

        for (let i = 0; i < semanas.length; i++) {

            const inicio = semanas[i].inicio;
            const fin = semanas[i].fin;

            if (!inicio || !fin) {

                Swal.fire({
                    icon: "warning",
                    title: "Validación",
                    text: `Debe ingresar la fecha inicio y fin de la Semana ${i + 1}`
                });

                return;
            }

            if (inicio > fin) {

                Swal.fire({
                    icon: "warning",
                    title: "Validación",
                    text: `La fecha inicio de la Semana ${i + 1} no puede ser mayor a la fecha fin`
                });

                return;
            }

            const fechaInicio = new Date(inicio);
            const fechaFin = new Date(fin);

            const diferenciaDias =
                Math.floor(
                    (fechaFin - fechaInicio)
                    /
                    (1000 * 60 * 60 * 24)
                ) + 1;

            if (diferenciaDias > 15) {

                Swal.fire({
                    icon: "warning",
                    title: "Rango inválido",
                    text: `La Semana ${i + 1} no puede superar los 15 días.`
                });

                return;
            }
        }

        /* ==========================================
        VALIDAR CRUCE DE SEMANAS
        ========================================== */

        for (let i = 0; i < semanas.length; i++) {

            const inicioA = new Date(semanas[i].inicio);
            const finA = new Date(semanas[i].fin);

            for (let j = i + 1; j < semanas.length; j++) {

                const inicioB = new Date(semanas[j].inicio);
                const finB = new Date(semanas[j].fin);

                const hayCruce =
                    inicioA <= finB &&
                    finA >= inicioB;

                if (hayCruce) {

                    Swal.fire({
                        icon: "warning",
                        title: "Semanas superpuestas",
                        text:
                            `La Semana ${i + 1} se cruza con la Semana ${j + 1}.`
                    });

                    return;
                }
            }
        }

        /* ==========================================
        VALIDAR ZONAS
        ========================================== */

        const zonas = obtenerZonas();

        if (!zonas || zonas.length === 0) {

            Swal.fire({
                icon: "warning",
                title: "Validación",
                text: "Debe seleccionar una zona."
            });

            return;
        }

        const payload = {

            zonas: zonas,

            agencia:
                document.getElementById("agencia").value,

            usuario:
                document.getElementById("oficial")
                    ? document.getElementById("oficial").value
                    : null,

            semanas: semanas
        };

        console.log("PAYLOAD:", payload);

        try {

            const response = await fetch(
                "index.php?url=resumen/comites-data",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                }
            );

            const data = await response.json();

            console.log("JSON:", data);

            /* ==========================================
            VALIDAR SI NO EXISTEN DATOS
            ========================================== */

            let totalFilas = 0;

            data.forEach(semana => {
                totalFilas += semana.length;
            });

            if (totalFilas === 0) {

                const tbody =
                    document.getElementById("tbodyResumen");

                if (tbody) {
                    tbody.innerHTML = "";
                }

                Swal.fire({
                    icon: "info",
                    title: "Sin resultados",
                    text: "Aún no existen comités registrados para los filtros seleccionados."
                });

                return;
            }

            generarTablaResumen(data);

            btnConsultar.disabled = true;
            btnExportar.disabled = false;

        } catch (error) {

            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No fue posible obtener la información del servidor."
            });
        }
    }
    function generarTablaResumen(data) {

        const contenedor =
            document.getElementById("contenedorTabla");

        let agencias = {};

        data.forEach((semana, idx) => {

            semana.forEach(a => {

                if (!agencias[a.id]) {

                    agencias[a.id] = {
                        nombre: a.nombre_agencia,
                        s1: 0,
                        s2: 0,
                        s3: 0,
                        s4: 0
                    };
                }

                agencias[a.id]["s" + (idx + 1)] =
                    parseInt(a.total);
            });
        });

        let html = `
            <table class="table table-bordered table-hover text-center">

                <thead class="table-success">

                    <tr>
                        <th>Agencia</th>
                        <th>Semana 01</th>
                        <th>Semana 02</th>
                        <th>Semana 03</th>
                        <th>Semana 04</th>
                        <th>Total</th>
                    </tr>

                </thead>

                <tbody>
        `;

        let totalS1 = 0;
        let totalS2 = 0;
        let totalS3 = 0;
        let totalS4 = 0;

        Object.values(agencias).forEach(a => {

            const total =
                a.s1 + a.s2 + a.s3 + a.s4;

            totalS1 += a.s1;
            totalS2 += a.s2;
            totalS3 += a.s3;
            totalS4 += a.s4;

            html += `
                <tr>

                    <td class="text-start">
                        ${a.nombre}
                    </td>

                    <td>${a.s1}</td>
                    <td>${a.s2}</td>
                    <td>${a.s3}</td>
                    <td>${a.s4}</td>

                    <td>
                        <b>${total}</b>
                    </td>

                </tr>
            `;
        });

        const totalGeneral =
            totalS1 + totalS2 + totalS3 + totalS4;

        html += `
            <tr class="table-warning">

                <th>TOTAL</th>

                <th>${totalS1}</th>
                <th>${totalS2}</th>
                <th>${totalS3}</th>
                <th>${totalS4}</th>

                <th>${totalGeneral}</th>

            </tr>
        `;

        html += `
                </tbody>
            </table>
        `;

        contenedor.innerHTML = html;
    }


    document.getElementById("btnExportar").addEventListener("click", exportarExcel);

    function exportarExcel() {

        const payload = {

            zonas: obtenerZonas(),

            agencia:
                document.getElementById("agencia").value,

            usuario:
                document.getElementById("oficial")
                    ? document.getElementById("oficial").value
                    : null,

            semanas: [

                {
                    inicio:
                        document.getElementById("s1i").value,

                    fin:
                        document.getElementById("s1f").value
                },

                {
                    inicio:
                        document.getElementById("s2i").value,

                    fin:
                        document.getElementById("s2f").value
                },

                {
                    inicio:
                        document.getElementById("s3i").value,

                    fin:
                        document.getElementById("s3f").value
                },

                {
                    inicio:
                        document.getElementById("s4i").value,

                    fin:
                        document.getElementById("s4f").value
                }

            ]
        };

        const form =
            document.createElement("form");

        form.method = "POST";

        form.action =
            "index.php?url=resumen/comites-excel";

        const input =
            document.createElement("input");

        input.type = "hidden";

        input.name = "payload";

        input.value =
            JSON.stringify(payload);

        form.appendChild(input);

        document.body.appendChild(form);

        form.submit();
    }
});