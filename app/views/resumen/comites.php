<?php
$title = "Resumen de Comités";

require __DIR__ . "/../layout/header.php";
?>

<style>
        /* ==========================================
    RESUMEN COMITES - FILTROS SEMANAS
    ========================================== */

    .card-filtro-semanas {
        border: 1px solid #d9dee3;
        border-radius: 10px;
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
    }

    .card-filtro-semanas .titulo-semana {
        color: #0d6efd;
        font-weight: 700;
        font-size: 15px;
        text-align: center;
        display: block;
        margin-bottom: 10px;
    }

    .card-filtro-semanas label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0;
    }

    .card-filtro-semanas .form-control,
    .card-filtro-semanas .form-select {
        height: 42px;
        border-radius: 6px;
    }

    .card-filtro-semanas .row {
        margin-bottom: 10px;
    }

    .card-filtro-semanas input[type="date"] {
        text-align: center;
    }

    @media (max-width: 992px) {

        .card-filtro-semanas .titulo-semana {
            margin-top: 10px;
        }

        .card-filtro-semanas label {
            margin-bottom: 5px;
            display: block;
        }
    }

    @media (max-width: 768px) {

        .card-filtro-semanas {
            padding: 15px;
        }

        .card-filtro-semanas .titulo-semana {
            font-size: 14px;
        }

        .card-filtro-semanas .form-control {
            margin-bottom: 8px;
        }
    }

    /* ==========================================
    TABLA RESUMEN
    ========================================== */

    #contenedorTabla table {
        font-size: 14px;
    }

    #contenedorTabla thead {
        background: #198754;
        color: white;
    }

    #contenedorTabla thead th {
        text-align: center;
        vertical-align: middle;
    }

    #contenedorTabla tbody td {
        vertical-align: middle;
    }

    #contenedorTabla tbody tr:last-child {
        background: #fff3cd;
        font-weight: bold;
    }

    /* ==========================================
    BOTONES
    ========================================== */

    #btnConsultar,
    #btnExportar,
    #btnCancelar {
        min-width: 140px;
        height: 42px;
        font-weight: 600;
    }
</style>
<div class="container-fluid">

    <h3 class="mb-4">
        Resumen de Comités
    </h3>

    <div class="row mb-4">

        <div class="col-md-3">
            <label>Zona</label>
            <select id="zona" class="form-select">
                <option value="">Todos</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Agencia</label>
            <select id="agencia" class="form-select">
                <option value="">Todas</option>
            </select>
        </div>

        <?php if ($_SESSION["user"]["rol"] === "admin"): ?>

        <div class="col-md-3">
            <label>Oficial</label>
            <select id="oficial" class="form-select">
                <option value="">Todos</option>
            </select>
        </div>

        <?php endif; ?>

    </div>

<!-- ======================================
     FECHAS POR SEMANA
====================================== -->

<div class="card-filtro-semanas">

<!-- CABECERAS -->

        <div class="row mb-2">

            <div class="col-md-2"></div>

            <div class="col-md-2 text-center">
                <strong class="titulo-semana">
                    Semana 01
                </strong>
            </div>

            <div class="col-md-2 text-center">
                <strong class="titulo-semana">
                    Semana 02
                </strong>
            </div>

            <div class="col-md-2 text-center">
                <strong class="titulo-semana">
                    Semana 03
                </strong>
            </div>

            <div class="col-md-2 text-center">
                <strong class="titulo-semana">
                    Semana 04
                </strong>
            </div>

        </div>

        <!-- FECHA INICIO -->

        <div class="row align-items-center mb-3">

            <div class="col-md-2">
                <label class="fw-semibold">
                    Fecha Inicio
                </label>
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s1i"
                    class="form-control">
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s2i"
                    class="form-control">
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s3i"
                    class="form-control">
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s4i"
                    class="form-control">
            </div>

        </div>

        <!-- FECHA FIN -->

        <div class="row align-items-center">

            <div class="col-md-2">
                <label class="fw-semibold">
                    Fecha Fin
                </label>
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s1f"
                    class="form-control">
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s2f"
                    class="form-control">
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s3f"
                    class="form-control">
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    id="s4f"
                    class="form-control">
            </div>

        </div>


</div>





    <div class="d-flex gap-2 mt-3 mb-3">
        <button
            id="btnConsultar"
            type="button"
            class="btn btn-success">
            <i class="bi bi-search"></i>
            Consultar
        </button>
        <button
            id="btnExportar"
            type="button"
            class="btn btn-primary"
            disabled>
            <i class="bi bi-file-earmark-excel"></i>
            Exportar Excel
        </button>
        <button
            id="btnCancelar"
            type="button"
            class="btn btn-secondary">

            <i class="bi bi-x-circle"></i>
            Cancelar
        </button>

    </div>

    <div id="contenedorTabla"></div>

</div>

<script src="assets/js/resumen_comites.js"></script>

<?php require __DIR__ . "/../layout/footer.php"; ?>