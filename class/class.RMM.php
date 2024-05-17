<?php

$RMM = new rmm();
class rmm
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
    public function Add_Mid($id,$temp)
    {
        $sql = "INSERT INTO `medicine_remm` 
            (user_id, quantity, descr, morning, afternoon, evening) 
        VALUES 
            (:user_id, :quantity, :descr, :morning, :afternoon, :evening)";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':quantity', $temp['quantity'], PDO::PARAM_INT);
        $stmt->bindParam(':descr', $temp['descr'], PDO::PARAM_STR);
        $stmt->bindParam(':morning', $temp['morning'], PDO::PARAM_STR);
        $stmt->bindParam(':afternoon', $temp['afternoon'], PDO::PARAM_STR);
        $stmt->bindParam(':evening', $temp['evening'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->fetchAll(PDO::FETCH_ASSOC);
        return true;
    }
    public function Get_Rem_Mid($id)
    {
        $sql = "SELECT * FROM `medicine_remm` 
                WHERE user_id = :user_id
                ORDER BY id DESC";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':user_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}