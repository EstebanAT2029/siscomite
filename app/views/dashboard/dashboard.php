<?php
$title = "Panel Principal";

require __DIR__ . "/../layout/header.php";
?>

<h3 class="dashboard-title mb-4">Panel Principal</h3>

<div class="row g-4">

    <!-- Registrar Comité -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-blue mb-2">
                <i class="bi bi-journal-plus"></i>
            </div>
            <h4 class="mb-1">Registrar Comité</h4>
            <p class="text-muted">Crear un nuevo comité</p>
            <a href="index.php?url=comite/form" class="btn btn-panel-blue w-100">Ingresar</a>
        </div>
    </div>

    <!-- Gestión de Oficiales -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-dark mb-2">
                <i class="bi bi-people-fill"></i>
            </div>
            <h4 class="mb-1">Gestión de Oficiales</h4>
            <p class="text-muted">Administrar Oficiales</p>
            <a href="index.php?url=oficiales" class="btn btn-panel-dark w-100">Administrar</a>
        </div>
    </div>

    <!-- Gestión de Jefes A -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-yellow mb-2">
                <i class="bi bi-person-gear"></i>
            </div>
            <h4 class="mb-1">Gestión de Jefes A</h4>
            <p class="text-muted">Administrar Jefes</p>
            <a href="index.php?url=jefes" class="btn btn-panel-yellow w-100 text-dark">Gestionar</a>
        </div>
    </div>

    <!-- Reporte General -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-green mb-2">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
            </div>
            <h4 class="mb-1">Actas Por Agencia</h4>
            <p class="text-muted">Actas Generadas por Agencia</p>
            <a href="index.php?url=reportes" class="btn btn-panel-green w-100">Visualizar</a>
        </div>
    </div>

    <!-- ========================= -->
    <!-- ✅ NUEVOS 4 MÓDULOS -->
    <!-- ========================= -->

    <!-- Reporte por Agencia -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-blue mb-2">
                <i class="bi bi-building"></i>
            </div>
            <h4 class="mb-1">Reporte de Actas</h4>
            <p class="text-muted">Reporte en Excel por Zona</p>
            <a href="index.php?url=reportes/general-excel" class="btn btn-panel-blue w-100">Generar Reporte</a>
            <!--<button type="button"
                class="btn btn-panel-blue w-100"
                data-bs-toggle="modal"
                data-bs-target="#modalEnConstruccion">
                Ver Resumen
            </button>-->
        </div>
    </div>

    <!-- Mantenimiento Acta -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-cyan mb-2">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <h4 class="mb-1">Mantenimiento Acta</h4>
            <p class="text-muted">Buscar, editar y reemitir actas</p>
            <a href="index.php?url=acta" class="btn btn-panel-cyan w-100">Gestionar</a>
        </div>
    </div>

    <!-- Resumen del Mes -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-green mb-2">
                <i class="bi bi-calendar2-week-fill"></i>
            </div>
            <h4 class="mb-1">Resumen del Mes</h4>
            <p class="text-muted">Indicadores y consolidado mensual</p>
            <a href="index.php?url=resumen/mes" class="btn btn-panel-green w-100">Ver Resumen</a>
        </div>
    </div>

    <!-- Otros -->
    <div class="col-md-3">
        <div class="panel-card p-4 text-center">
            <div class="icon-box icon-yellow mb-2">
                <i class="bi bi-grid-1x2-fill"></i>
            </div>
            <h4 class="mb-1">Otros</h4>
            <p class="text-muted">Herramientas y utilidades</p>
            <a href="index.php?url=otros" class="btn btn-panel-yellow w-100 text-dark">Ingresar</a>
        </div>
    </div>

</div>
<!-- MODAL: MÓDULO EN CONSTRUCCIÓN -->
<div class="modal fade" id="modalEnConstruccion"
     tabindex="-1"
     data-bs-backdrop="static"
     data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">

            <div class="modal-header border-0 text-center">
                <h5 class="modal-title w-100 fw-bold">
                    🚧 Módulo en Construcción
                </h5>
            </div>

            <div class="modal-body text-center">
                <p class="mb-3 text-muted">
                    El <b>Resumen del Mes</b> se encuentra actualmente en desarrollo.<br>
                    Estará disponible en una próxima versión del sistema.
                </p>
            </div>

            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-success w-100"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>

        </div>
    </div>
</div>

<?php require __DIR__ . "/../layout/footer.php"; ?>
