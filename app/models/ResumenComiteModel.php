<?php

class ResumenComiteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /* =====================================================
       ZONAS DEL USUARIO
    ===================================================== */
    public function getZonasUsuario($idUsuario)
    {
        $sql = "
            SELECT
                z.id,
                z.nombre
            FROM usuario_zona uz
            INNER JOIN zonas z
                ON z.id = uz.id_zona
            WHERE uz.id_usuario = ?
            AND uz.estado = 1
            ORDER BY z.nombre
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* =====================================================
       AGENCIAS POR ZONA
    ===================================================== */
    public function getAgenciasPorZonas(array $zonas)
    {
        if (empty($zonas)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($zonas), '?'));

        $sql = "
            SELECT
                id,
                nombre_agencia
            FROM agencia
            WHERE id_zona IN ($placeholders)
            ORDER BY nombre_agencia ASC
        ";

        $stmt = $this->db->prepare($sql);

        $types = str_repeat("i", count($zonas));

        $stmt->bind_param($types, ...$zonas);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* =====================================================
       OFICIALES ACTIVOS
    ===================================================== */
    public function getUsuarios()
    {
        $sql = "
            SELECT
                id,
                CONCAT(apellidos,' ',nombres) AS nombre,
                rol
            FROM usuarios
            WHERE estado = 1
            ORDER BY apellidos,nombres
        ";

        return $this->db
            ->query($sql)
            ->fetch_all(MYSQLI_ASSOC);
    }


    /* =====================================================
    CONTAR COMITÉS POR AGENCIA
    ===================================================== */
    public function contarComites(
        $fechaInicio,
        $fechaFin,
        $zonas,
        $agencia = null,
        $usuario = null
        )
        {
            $where = [];
            $params = [];
            $types = "";

            $where[] = "c.fecha BETWEEN ? AND ?";
            $params[] = $fechaInicio;
            $params[] = $fechaFin;
            $types .= "ss";

        if (!empty($zonas)) {

            $in = implode(',', array_fill(0, count($zonas), '?'));

            $where[] = "a.id_zona IN ($in)";

            foreach ($zonas as $z) {
                $params[] = $z;
                $types .= "i";
            }
        }

        if (!empty($agencia)) {
            $where[] = "c.id_agencia = ?";
            $params[] = $agencia;
            $types .= "i";
        }

        if (!empty($usuario)) {
            $where[] = "c.id_usuario = ?";
            $params[] = $usuario;
            $types .= "i";
        }

        $sql = "
            SELECT
                a.id,
                a.nombre_agencia,
                COUNT(*) AS total

            FROM comite c

            INNER JOIN agencia a
                ON a.id = c.id_agencia

            WHERE " . implode(" AND ", $where) . "

            GROUP BY
                a.id,
                a.nombre_agencia

            ORDER BY
                a.nombre_agencia ASC
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            throw new Exception($this->db->error);
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getNombreZona($idZona)
    {
        $sql = "
            SELECT nombre
            FROM zonas
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param("i", $idZona);

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();
    }
}