<?php

class DetalleComiteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /* ============================================================
       INSERTAR DETALLE DEL COMITÉ
    ============================================================ */
    public function insertarDetalle($data)
    {
        $sql = "INSERT INTO detalle_comite (
            id_comite,
            id_agencia,
            correlativo,
            dni,
            cadena,
            nombres,
            monto,
            tipo_cli,
            tipo_credito,
            id_oficial_proponente,
            id_oficial_participante1,
            id_oficial_participante2,
            id_jefe_agencia,
            id_criterio,
            id_decision,
            observaciones
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error preparando insertarDetalle: " . $this->db->error);
        }

        $stmt->bind_param(
            "iissssdsisiiiiis",
            $data["id_comite"],
            $data["id_agencia"],
            $data["correlativo"],
            $data["dni"],
            $data["cadena"],
            $data["nombres"],
            $data["monto"],
            $data["tipo_cli"],
            $data["tipo_credito"],
            $data["id_oficial_proponente"],
            $data["id_of1"],
            $data["id_of2"],
            $data["id_jefe_agencia"],
            $data["id_criterio"],
            $data["id_decision"],
            $data["observaciones"]
        );

        if (!$stmt->execute()) {
            throw new Exception("Error insertando detalle: " . $stmt->error);
        }

        return $stmt->insert_id;
    }

    /* ============================================================
    TRAER DETALLE POR ID (para ACTA PDF / WORD)
    ============================================================ */
    public function getById($id)
    {
        $sql = "
            SELECT 
                d.*,
                ds.descripcion AS decision_desc,
                CONCAT(o1.apellidos, ' ', o1.nombres) AS oficial_proponente_nombre,
                cc.codigo AS criterio_codigo
            FROM detalle_comite d
            LEFT JOIN decision ds 
                ON ds.id = d.id_decision
            LEFT JOIN oficiales_negocios o1 
                ON o1.id = d.id_oficial_proponente
            LEFT JOIN criterios_comite cc ON cc.id = d.id_criterio
            WHERE d.id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /* ============================================================
       OBTENER CORRELATIVO POR AGENCIA + AÑO
       (Usa fecha del comité, no fecha inexistente)
    ============================================================ */
    public function getCorrelativoAgenciaAnio($idAgencia, $anio)
    {
        $sql = "SELECT IFNULL(MAX(correlativo), 0) AS ultimo
                FROM detalle_comite dc
                INNER JOIN comite c ON c.id = dc.id_comite
                WHERE dc.id_agencia = ?
                AND YEAR(c.fecha) = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $idAgencia, $anio);
        $stmt->execute();

        $res = $stmt->get_result()->fetch_assoc();
        return intval($res["ultimo"]) + 1;
    }

    /* ============================================================
       VALIDAR ZONA DE AGENCIA
       (Para restringir registros por usuario)
    ============================================================ */
    public function getZonaDeAgencia($idAgencia)
    {
        $sql = "SELECT id_zona FROM agencia WHERE id = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idAgencia);
        $stmt->execute();

        $res = $stmt->get_result()->fetch_assoc();

        return $res["id_zona"] ?? null;
    }

    public function getByComite($idComite)
    {
        $sql = "SELECT d.*, 
                    ds.descripcion AS decision_desc,
                    CONCAT(o.apellidos, ' ', o.nombres) AS oficial_proponente_nombre,
                    cc.codigo AS criterio_codigo
                FROM detalle_comite d
                LEFT JOIN decision ds ON ds.id = d.id_decision
                LEFT JOIN oficiales_negocios o ON o.id = d.id_oficial_proponente
                LEFT JOIN criterios_comite cc ON cc.id = d.id_criterio
                WHERE d.id_comite = ?
                ORDER BY d.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idComite);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}