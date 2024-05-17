<?php

class mo
{
    public $conn;

    public function __construct()
    {
        $dsn = "mysql:host=localhost;dbname=dr_cancer;";
        $user = "root";
        $pass = '';
        $option = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );
        try {
            $this->conn = new PDO($dsn, $user, $pass, $option); //start connection
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Failed To Connect " . $e->getMessage();
        }
    }
}

