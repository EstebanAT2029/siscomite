<?php require __DIR__ . "/../layout/header.php"; ?>

<div class="container mt-4">

    <h3>Reporte de Comité de Créditos</h3>

    <!-- FILTROS -->
    <form method="GET" action="index.php" class="row g-3 mb-4">
        <input type="hidden" name="url" value="reportes">

        <div class="col-md-4">
            <label class="form-label">Agencia</label>
            <select name="agencia" class="form-select">
                <option value="">Seleccione...</option>
                <?php foreach ($agencias as $a): ?>
                    <option value="<?= (int)$a['id'] ?>"
                        <?= (!empty($_GET['agencia']) && $_GET['agencia'] == $a['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['nombre_agencia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Buscar</button>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <a href="index.php?url=reportes/general-excel" class="btn btn-success w-100">
                📘 Reporte General
            </a>
        </div>

    </form>

    <!-- TABLA -->
    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Cadena</th>
                        <th>Agencia</th>
                        <th>DNI</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Resolución</th>
                        <th style="min-width: 220px;">Observaciones</th>
                        <th>Oficial Riesgos</th>
                        <th>Oficial Proponente</th>
                        <th>N° Acta</th>
                        <th>Acta</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($resultados)): ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted">Sin resultados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($resultados as $r): ?>
                            <?php
                                $obs = $r["observaciones"] ?? "";
                                $obs = is_string($obs) ? trim($obs) : "";
                                $preview = mb_substr($obs, 0, 30, 'UTF-8');
                                $hayMas = mb_strlen($obs, 'UTF-8') > 30;

                                $tituloCaso = "Cadena " . ($r["cadena"] ?? "") . " | DNI " . ($r["dni"] ?? "") . " | " . ($r["cliente"] ?? "");
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($r["cadena"]) ?></td>
                                <td><?= htmlspecialchars($r["nombre_agencia"]) ?></td>
                                <td><?= htmlspecialchars($r["dni"]) ?></td>
                                <td><?= htmlspecialchars($r["cliente"]) ?></td>
                                <td><?= number_format((float)$r["monto"], 2) ?></td>
                                <td><?= htmlspecialchars($r["fecha"]) ?></td>
                                <td><?= htmlspecialchars($r["resolucion"]) ?></td>

                                <!-- OBSERVACIONES (preview + modal) -->
                                <td class="text-wrap">
                                    <?php if ($obs === ""): ?>
                                        <span class="text-muted">—</span>
                                    <?php else: ?>
                                        <span>
                                            <?= htmlspecialchars($preview) ?><?= $hayMas ? "..." : "" ?>
                                        </span>

                                        <?php if ($hayMas): ?>
                                            <button
                                                type="button"
                                                class="btn btn-link btn-sm p-0 ms-2 ver-mas"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalObservaciones"
                                                data-obs="<?= htmlspecialchars($obs, ENT_QUOTES, 'UTF-8') ?>"
                                                data-titulo="<?= htmlspecialchars($tituloCaso, ENT_QUOTES, 'UTF-8') ?>"
                                                title="Ver observaciones completas"
                                            >
                                                Ver más <i class="bi bi-box-arrow-up-right"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($r["usuario_registro"]) ?></td>
                                <td><?= htmlspecialchars($r["oficial_proponente"]) ?></td>
                                <td><?= htmlspecialchars($r["correlativo"]) ?></td>

                                <td>
                                    <a target="_blank"
                                       href="index.php?url=comite/acta&id=<?= (int)$r['id_comite'] ?>&id_detalle=<?= (int)$r['id_detalle'] ?>"
                                       class="btn btn-sm btn-primary mb-1">
                                        PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
            <?php if (!empty($_GET["agencia"]) && !empty($totalPages) && $totalPages > 1): ?>
            <?php
                $baseParams = $_GET;
                $baseParams["url"] = $baseParams["url"] ?? "reportes";

                $start = max(1, $page - 2);
                $end   = min($totalPages, $page + 2);
            ?>

            <nav class="mt-3">
                <ul class="pagination justify-content-center">

                    <!-- Anterior -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <?php $baseParams["page"] = max(1, $page - 1); ?>
                        <a class="page-link" href="index.php?<?= http_build_query($baseParams) ?>">Anterior</a>
                    </li>

                    <!-- Páginas -->
                    <?php for ($p = $start; $p <= $end; $p++): ?>
                        <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                            <?php $baseParams["page"] = $p; ?>
                            <a class="page-link" href="index.php?<?= http_build_query($baseParams) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Siguiente -->
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <?php $baseParams["page"] = min($totalPages, $page + 1); ?>
                        <a class="page-link" href="index.php?<?= http_build_query($baseParams) ?>">Siguiente</a>
                    </li>

                </ul>

                <div class="text-center text-muted small">
                    Página <?= (int)$page ?> de <?= (int)$totalPages ?> — Total registros: <?= (int)$total ?>
                </div>
            </nav>
        <?php endif; ?>


        </div>
    </div>

</div>

<!-- MODAL OBSERVACIONES -->
<div class="modal fade" id="modalObservaciones" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modalObsTitle">Observaciones</h5>
          <div class="small text-muted" id="modalObsSubTitle"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="p-3 bg-light rounded-3"
             style="max-height: 55vh; overflow:auto; white-space: pre-wrap; line-height: 1.45;">
          <span id="modalObsText"></span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Ver menos
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const modalTitle = document.getElementById("modalObsTitle");
  const modalSubTitle = document.getElementById("modalObsSubTitle");
  const modalText = document.getElementById("modalObsText");

  document.querySelectorAll(".ver-mas").forEach(btn => {
    btn.addEventListener("click", () => {
      const obs = btn.getAttribute("data-obs") || "";
      const titulo = btn.getAttribute("data-titulo") || "";

      modalTitle.textContent = "Observaciones";
      modalSubTitle.textContent = titulo;
      modalText.textContent = obs;
    });
  });
});
</script>

<?php require __DIR__ . "/../layout/footer.php"; ?>
