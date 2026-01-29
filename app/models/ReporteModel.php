<?php

class ReporteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * REPORTE POR AGENCIA (FILTRADO POR ZONA DEL USUARIO)
     */
    public function reportePorAgencia($idAgencia, $idZona)
    {
        $sql = "
            SELECT 
                d.id AS id_detalle,
                d.cadena,
                a.nombre_agencia,
                d.dni,
                d.nombres AS cliente,
                d.monto,
                c.fecha,

                CASE d.id_decision
                    WHEN 1 THEN 'Aprobado'
                    WHEN 2 THEN 'Observado'
                    WHEN 3 THEN 'Denegado'
                    ELSE 'Sin decisión'
                END AS resolucion,

                d.observaciones,

                CONCAT(u.apellidos, ' ', u.nombres) AS usuario_registro,

                c.id AS id_comite,
                d.correlativo,

                CONCAT(op.apellidos, ' ', op.nombres) AS oficial_proponente

            FROM detalle_comite d
            INNER JOIN comite c ON c.id = d.id_comite
            INNER JOIN agencia a ON a.id = d.id_agencia
            INNER JOIN usuarios u ON u.id = c.id_usuario
            LEFT JOIN oficiales_negocios op ON op.id = d.id_oficial_proponente

            WHERE d.id_agencia = ?
              AND a.id_zona = ?

            ORDER BY c.fecha DESC, d.cadena ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $idAgencia, $idZona);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * AGENCIAS DISPONIBLES SEGÚN ZONA DEL USUARIO
     */
    public function agencias($idZona)
    {
        $sql = "
            SELECT id, nombre_agencia
            FROM agencia
            WHERE id_zona = ?
            ORDER BY nombre_agencia ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idZona);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * REPORTE GENERAL (MISMO SQL, SOLO FILTRO POR ZONA)
     */
    public function reporteGeneral($idZona)
    {
        $sql = "
            SELECT 
                d.cadena AS Cadena,
                CONCAT(u.apellidos, ' ', u.nombres) AS Oficial_Riesgos,
                a.nombre_agencia AS Agencia,
                d.dni AS DNI,
                d.nombres AS Cliente,
                d.tipo_cli AS TipoClie,
                d.monto AS Monto,
                DATE_FORMAT(c.fecha, '%d/%m/%Y') AS Fecha,
                cc.codigo AS Criterio,
                ds.descripcion AS Resolucion,
                d.observaciones AS Comentarios,
                CONCAT(op.apellidos, ' ', op.nombres) AS Oficial_Proponente,
                d.correlativo AS Num_Acta,

                rv.dni AS DNI_RV,
                CONCAT(rv.apellidos, ' ', rv.nombres) AS Nombres_RV,
                rv.grado_consanguinidad AS Parentesco,
                rv.domicilio_si AS RV_Por_Domicilio_,
                rv.domicilio_texto AS Domicilio_RV,
                rv.actividad_si AS RV_Por_Actividad,
                rv.actividad_texto AS Actividad_RV,
                rv.predio_si AS RV_Por_Predio,
                rv.predio_texto AS Predio_RV

            FROM comite c
            JOIN detalle_comite d ON d.id_comite = c.id
            JOIN agencia a ON a.id = d.id_agencia
            JOIN decision ds ON ds.id = d.id_decision
            JOIN usuarios u ON u.id = c.id_usuario

            LEFT JOIN oficiales_negocios op
                ON op.id = d.id_oficial_proponente

            LEFT JOIN criterios_comite cc
                ON cc.id = d.id_criterio
            LEFT JOIN riesgo_vinculado rv 
                ON rv.id_detalle_comite = d.id

            WHERE a.id_zona = ?

            ORDER BY c.fecha DESC, d.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idZona);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
        /**
     * CONTAR REGISTROS PARA PAGINACIÓN
     * (MISMO FILTRO QUE reportePorAgencia)
     */
    public function countReportePorAgencia($idAgencia, $idZona)
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM detalle_comite d
            INNER JOIN agencia a ON a.id = d.id_agencia
            WHERE d.id_agencia = ?
              AND a.id_zona = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $idAgencia, $idZona);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        return (int)$row["total"];
    }

    /**
     * REPORTE POR AGENCIA PAGINADO
     * (MISMO SQL QUE reportePorAgencia + LIMIT / OFFSET)
     */
    public function reportePorAgenciaPaginado($idAgencia, $idZona, $limit, $offset)
    {
        $sql = "
            SELECT 
                d.id AS id_detalle,
                d.cadena,
                a.nombre_agencia,
                d.dni,
                d.nombres AS cliente,
                d.monto,
                c.fecha,

                CASE d.id_decision
                    WHEN 1 THEN 'Aprobado'
                    WHEN 2 THEN 'Observado'
                    WHEN 3 THEN 'Denegado'
                    ELSE 'Sin decisión'
                END AS resolucion,

                d.observaciones,

                CONCAT(u.apellidos, ' ', u.nombres) AS usuario_registro,

                c.id AS id_comite,
                d.correlativo,

                CONCAT(op.apellidos, ' ', op.nombres) AS oficial_proponente

            FROM detalle_comite d
            INNER JOIN comite c ON c.id = d.id_comite
            INNER JOIN agencia a ON a.id = d.id_agencia
            INNER JOIN usuarios u ON u.id = c.id_usuario
            LEFT JOIN oficiales_negocios op ON op.id = d.id_oficial_proponente

            WHERE d.id_agencia = ?
              AND a.id_zona = ?

            ORDER BY c.fecha DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iiii", $idAgencia, $idZona, $limit, $offset);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}