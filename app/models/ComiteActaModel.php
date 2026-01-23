<?php

class ComiteActaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /* ===============================================
       DATOS PARA ACTA
    ================================================ */
    public function getDatosActa($idComite)
    {
    $sqlEnc = "
        SELECT 
            c.id,
            c.hora,
            c.fecha,
            c.numero_casos,
            a.nombre_agencia,
            z.nombre AS zona,
            d.id AS id_detalle_comite,
            d.correlativo,
            d.id_criterio,
            cc.codigo AS criterio_codigo,

            CONCAT(j.apellidos,' ',j.nombres) AS jefe_agencia,
            CONCAT(op1.apellidos,' ',op1.nombres) AS oficial_participante1,
            CONCAT(op2.apellidos,' ',op2.nombres) AS oficial_participante2,
            CONCAT(oprop.apellidos,' ',oprop.nombres) AS oficial_proponente,
            CONCAT(u.apellidos,' ',u.nombres) AS oficial_riesgos

        FROM comite c
        JOIN detalle_comite d ON d.id_comite = c.id
        JOIN agencia a ON a.id = d.id_agencia
        JOIN zonas z ON z.id = a.id_zona

        LEFT JOIN jefes_agencia j ON j.id = d.id_jefe_agencia
        LEFT JOIN oficiales_negocios op1 ON op1.id = d.id_oficial_participante1
        LEFT JOIN oficiales_negocios op2 ON op2.id = d.id_oficial_participante2
        LEFT JOIN oficiales_negocios oprop ON oprop.id = d.id_oficial_proponente
        LEFT JOIN usuarios u ON u.id = c.id_usuario
        LEFT JOIN criterios_comite cc ON cc.id = d.id_criterio

        WHERE c.id = ?
        LIMIT 1
    ";


        $stmt = $this->db->prepare($sqlEnc);
        $stmt->bind_param("i", $idComite);
        $stmt->execute();
        $enc = $stmt->get_result()->fetch_assoc();

        if (!$enc) return null;

        /* ===============================================
        CASOS
        ================================================ */

        $sqlCasos = "
                SELECT
                    d.cadena,
                    d.dni,
                    d.nombres,
                    d.tipo_cli,
                    d.tipo_credito,
                    d.monto,
                    d.observaciones,
                    cc.codigo AS criterio_codigo,
                    (SELECT descripcion FROM decision WHERE id = d.id_decision) AS decision
                FROM detalle_comite d
                LEFT JOIN criterios_comite cc ON cc.id = d.id_criterio
                WHERE d.id_comite = ?
                ORDER BY d.id ASC
            ";


        $stmt2 = $this->db->prepare($sqlCasos);
        $stmt2->bind_param("i", $idComite);
        $stmt2->execute();

        $casos = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'encabezado' => $enc,
            'casos'      => $casos
        ];
    }

}