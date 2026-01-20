<?php

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            date_default_timezone_set('America/Lima');
            
            $host = "localhost";
            $user = "root";
            $pass = "";
            $dbname = "comite_creditos";
            $port = 3306;
            
            self::$instance = new mysqli($host, $user, $pass, $dbname, $port);

            if (self::$instance->connect_error) {
                die("Error de conexi贸n: " . self::$instance->connect_error);
            }

            self::$instance->set_charset("utf8mb4");

        }

        return self::$instance;
    }
}