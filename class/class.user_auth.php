<?php

$USAObj = new user_auth();
class user_auth
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
    //****************************************** CHECK ******************************************
    //****************************************** CHECK ******************************************
    //****************************************** CHECK ******************************************
    //****************************************** CHECK ******************************************
    //****************************************** CHECK ******************************************
    public function checkIfEmailExist($a, $email)
    {
        if ($a == 0) {
            $sql = "SELECT * FROM `users` WHERE `email` = :email";
            $query = $this->mo->conn->prepare($sql);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $query->debugDumpParams();
            return $query->fetch(PDO::FETCH_ASSOC);
        } elseif ($a == 1) {
            $deleteSql = "DELETE FROM `verification` WHERE `email` = :email";
            $deleteQuery = $this->mo->conn->prepare($deleteSql);
            $deleteQuery->bindParam(':email', $email, PDO::PARAM_STR);
            $deleteQuery->execute();
        }
    }
    public function generatePasswordResetToken()
    {
        $code = random_int(100000, 999999); // Generate a random number between 100000 and 999999 (inclusive)
        return (string) $code; // Convert the integer to string
    }
    public function storePasswordResetToken($email, $code)
    {
        $sql = "INSERT INTO verification SET code = :code, email = :email";
        $query = $this->mo->conn->prepare($sql);
        $query->bindParam(':code', $code, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        return $query->rowCount() > 0;
    }

    //****************************************** LOGIN ******************************************
    //****************************************** LOGIN ******************************************
    //****************************************** LOGIN ******************************************
    //****************************************** LOGIN ******************************************
    //****************************************** LOGIN ******************************************
    public function checkPassword($email, $password)
    {
        $sql = "SELECT `id` FROM `users` ";
        $sql .= " WHERE  `email` = :email AND `password` = :password ";

        $query = $this->mo->conn->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLastLoging($user_id)
    {
        $sql = "UPDATE `users` SET `last_login_date` = NOW() WHERE `id` = :user_id";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getUserDetailsInfo($id)
    {
        $result = array();

        $sql = "SELECT u.user_name, u.email,  u.password, u.code ,u.type";
        $sql .= " FROM users u ";
        $sql .= " WHERE u.id = :id ";

        $query = $this->mo->conn->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    //****************************************** REGISTER ******************************************
    //****************************************** REGISTER ******************************************
    //****************************************** REGISTER ******************************************
    //****************************************** REGISTER ******************************************
    //****************************************** REGISTER ******************************************

    public function register($password, $temp)
    {
        // Insert into users table
        $sql = "INSERT INTO `users` (email, password, user_name,type,lat,lon) ";
        $sql .= "VALUES (:email, :password, :name ,:type,:lat,:lon)";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':email', $temp['email']);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':name', $temp['name']);
        $stmt->bindParam(':type', $temp['type']);
        $stmt->bindParam(':lat', $temp['lat']);
        $stmt->bindParam(':lon', $temp['lon']);
        $stmt->execute();
        $user_id = $this->mo->conn->lastInsertId();

        if ($temp['type'] == "doctor") {
            $sql1 = "INSERT INTO dr_type SET";
            $sql1 .= " user_id = :user_id";
            $sql1 .= ",dr_type = :dr_type ";
            $stmt->bindParam(':email', $temp['dr_type']);
            $stmt->bindParam(':email', $user_id);
            $stmt->execute();
        }
        return $user_id;
    }

    public function makeAuthCode($id)
    {
        $randomBytes = random_bytes(32);
        $token = base64_encode($randomBytes);
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $token);
        $sql = "UPDATE `users` SET `code` = :token WHERE `id` = :id ";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    //****************************************** FORGET PASS ******************************************
    //****************************************** FORGET PASS ******************************************
    //****************************************** FORGET PASS ******************************************
    //****************************************** FORGET PASS ******************************************
    //****************************************** FORGET PASS ******************************************
    public function check_code($email, $code)
    {
        $sql = "SELECT * FROM `verification` WHERE `email` = :email AND `code` = :code";
        $query = $this->mo->conn->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':code', $code, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($email, $newPassword)
    {
        $sql = "UPDATE users SET `password` = :password WHERE email = :email";
        $query = $this->mo->conn->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $newPassword, PDO::PARAM_STR); // Bind the new password parameter
        $query->execute();

        return $query->rowCount() > 0;
    }

    //****************************************** HEADER INFO ******************************************
    //****************************************** HEADER INFO ******************************************
    //****************************************** HEADER INFO ******************************************
    //****************************************** HEADER INFO ******************************************
    //****************************************** HEADER INFO ******************************************

    public function getUserDataByAuth($authentication_code)
    {
        $sql = "SELECT u.*
                FROM users u
                WHERE u.code = :authentication_code";

        // Prepare and execute the query
        $query = $this->mo->conn->prepare($sql);
        $query->bindParam(':authentication_code', $authentication_code, PDO::PARAM_STR);
        $query->execute();

        // Fetch user data
        $userData = $query->fetch(PDO::FETCH_ASSOC);

        return $userData;
    }

    // public function getUserDataByID($id)
    // {
    //     // Query to retrieve the page_type from user_auth table
    //     $pageTypeQuery = "SELECT * FROM user_auth WHERE id = :id";
    //     $pageTypeStmt =$conn->prepare($pageTypeQuery);
    //     $pageTypeStmt->bindParam(':id', $id, PDO::PARAM_STR);
    //     $pageTypeStmt->execute();
    //     $pageType = $pageTypeStmt->fetch(PDO::FETCH_ASSOC);

    //     if ($pageType['page_type'] === 'user') {
    //         $sql = "SELECT u.*
    //         FROM {$this->mPrefix}users u
    //         WHERE u.id = '{$pageType['user_id']}'";

    //     }

    //     // Execute the main query
    //     $query =$conn->prepare($sql);
    //     $query->$conn->bindParam(':id', $id, PDO::PARAM_STR);
    //     $query->execute();

    //     $userData = $query->fetch(PDO::FETCH_ASSOC);

    //     // Merge the user data and page type information
    //     $mergedData = array_merge($userData, $pageType);

    //     return $mergedData;

    // }

}
