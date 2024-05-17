<?php

$DR = new doc();
class doc
{
    public $mo;
    public $lang;
    public $dateTime;

    public function __construct()
    {
        $this->mo = new mo();
        $this->lang = defined('LANGUAGE') ? LANGUAGE : null;
        $this->dateTime = date('Y-m-d H:i:s');
    }
    public function Get_Dr($lat,$lon,$temp){
        $sql = "SELECT 
                    u.user_name,
                    u.img,
                    (6371 * acos(cos(radians($lat)) * cos(radians(u.lat)) * cos(radians(u.lon) - radians($lon)) + sin(radians($lat)) * sin(radians(u.lat)))) AS distance
                FROM users u
                LEFT JOIN dr_type d ON d.user_id = u.id
                WHERE u.type = 'doctor' AND d.dr_type = :type
                ";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':type', $temp['type'], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}