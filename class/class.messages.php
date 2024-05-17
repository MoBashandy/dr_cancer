<?php

$message = new message();

class message
{

    public $mo;
    public $dateTime;

    public function __construct()
    {

        $this->mo = new mo();
        $this->dateTime = date('Y-m-d H:i:s');
    }
    public function checkIfIhaveAlreadyConversation($id, $user_id)
    {
        $sql = "SELECT id FROM `conversation`
                WHERE (user1 = :user_id OR user2 = :user_id) AND (user1 = :id OR user2 = :id)";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
    public function addConversationApi($id, $user_id)
    {
        $sql = "INSERT INTO `conversation`
                    (user1, user2, last_updated, date_added)
                VALUES
                    (:id, :user_id, NOW(), NOW())";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $lastId = $this->mo->conn->lastInsertId();

        $temp['conversation_id'] = $lastId;
        $temp['from'] = $id;
        $temp['to'] = $user_id;
        if ($lastId) {
            return $this->insertMessageApi($temp);
        }
    }
    public function insertMessageApi($temp)
    {
        $sql = "INSERT INTO `messages` (`conversation_id`, `from`,`to`,  `date_added`, `seen_it`) ";
        $sql .= "VALUES (:conversation_id, :from,:to,  NOW(), '0')";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $temp['conversation_id'], PDO::PARAM_INT);
        $stmt->bindParam(':from', $temp['from'], PDO::PARAM_STR);
        $stmt->bindParam(':to', $temp['to'], PDO::PARAM_STR);
        $success = $stmt->execute();

        if ($success) {
            $id = $this->mo->conn->lastInsertId();
            $this->updateConversationApi($temp['conversation_id']);
            return $this->getOneMessage($id);
        } else {
            return false;
        }
    }

    public function updateConversationApi($id)
    {
        $sql = "UPDATE `conversation` SET `last_updated` = NOW() WHERE `id` = :id";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function getOneMessage($id)
    {
        $sql = "SELECT m.`id`, m.`conversation_id`, m.`message`, m.`date_added`, m.`seen_it`, m.`from`, m.`direction`, m.`to`, m.`type` ";
        $sql .= "FROM `messages` m ";
        $sql .= "WHERE m.`id` = :id ";
        $sql .= "LIMIT 1";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res;
    }
    public function getAllConversationByUserIdApi($aId = 0, $aTo = 0)
    {
        $sql = "SELECT C.`id`, C.`user1`, C.`user2` FROM `conversation` C ";
        $sql .= " LEFT JOIN `users` U1 ON (U1.id = C.user1)";
        $sql .= " LEFT JOIN `users` U2 ON (U2.id = C.user2)";
        $sql .= " LEFT JOIN `messages` M ON (C.`id` = M.`conversation_id`)";
        $sql .= " WHERE (C.`user1`=:userId OR C.user2=:userId)";
        $sql .= $aTo ? " AND (C.`user1` != :aTo AND C.`user2` != :aTo)" : "";
        $sql .= " GROUP BY C.`id`";
        $sql .= " ORDER BY last_updated DESC";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':userId', $aId, PDO::PARAM_INT);
        if ($aTo) {
            $stmt->bindParam(':aTo', $aTo, PDO::PARAM_INT);
        }
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ret = [];
        if ($res) {
            $count = count($res);
            for ($i = 0; $i < $count; $i++) {
                $info = $this->getLastMessageInfoInConverstationApi($res[$i]['id'], $aId);
                if ($info['date_added']) {
                    $reciver_id = $res[$i]['user1'] == $aId ? $res[$i]['user2'] : $res[$i]['user1'];
                    $Reciver = $this->getReciverInfoByIdApi($reciver_id);
                    $res[$i]['unreadMessageCount'] = $this->getUnreadMessageCountApi($aId, $res[$i]['id']);
                    $res[$i]['seen_it'] = $info['seen_it'];
                    $res[$i]['type'] = $info['type'];
                    $res[$i]['date_added'] = $info['date_added'];
                    $res[$i]['messageType'] = $info['senderMessage'] == $aId ? 'send' : 'recive';
                    $res[$i]['message'] = $this->trim_text($info['message'], 30);
                    $res[$i]['user_name'] = $Reciver['user_name'];
                    $res[$i]['img'] = $Reciver['img'];
                    $res[$i]['to'] = $reciver_id;
                    unset($res[$i]['user1'], $res[$i]['user2']);
                } else {
                    unset($res[$i]);
                }
            }
        }
        $ret['conversations'] = array_values($res);
        $ret['count'] = $count;
        return $ret;
    }
    public function getLastMessageInfoInConverstationApi($conversation_id = 0, $aUserId = 0)
    {
        $deleteConversationDate = $this->getDeleteDateByConversation_idAndUser_id($conversation_id, $aUserId);
        $deleteConversationDate = $deleteConversationDate ? $deleteConversationDate : '1970-01-01';

        $sql = "SELECT m.message, m.date_added, m.seen_it, m.from AS senderMessage, m.type";
        $sql .= " FROM `messages` m";
        $sql .= " WHERE m.`conversation_id` = :conversation_id";
        $sql .= " AND m.`date_added` > :deleteConversationDate";
        $sql .= " AND ((m.`from` = :userId AND m.to != :userId AND m.del_from = '0')";
        $sql .= " OR (m.`from` != :userId AND m.to = :userId AND m.del_to = '0'))";
        $sql .= " ORDER BY m.`id` DESC";
        $sql .= " LIMIT 1";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':deleteConversationDate', $deleteConversationDate);
        $stmt->bindParam(':userId', $aUserId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res;
    }
    public function getDeleteDateByConversation_idAndUser_id($conversation_id = 0, $aUserId = 0)
    {
        $userColumn = $this->getUser1ByConversationId($conversation_id) == $aUserId ? "date_delete_form_user1" : "date_delete_form_user2";
        $sql = "SELECT $userColumn FROM `conversation` WHERE id = :conversation_id";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getUser1ByConversationId($conversation_id)
    {
        $sql = "SELECT user1 FROM `conversation` WHERE id = :conversation_id";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getReciverInfoByIdApi($aId)
    {
        $sql = "SELECT user_name, img ";
        $sql .= "FROM `users` ";
        $sql .= "WHERE `id` = :id";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':id', $aId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getUnreadMessageCountApi($aUserId, $conversation_id)
    {
        $sql = "SELECT COUNT(id) FROM `messages` WHERE `to` = :user_id AND `seen_it` = '0'";
        if (!empty($conversation_id)) {
            $sql .= " AND conversation_id = :conversation_id";
        }

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':user_id', $aUserId, PDO::PARAM_INT);
        if (!empty($conversation_id)) {
            $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function messageHasSeen($request)
    {
        $sql = "UPDATE `messages` SET `seen_it` = '1' WHERE `id` = :message_id";
        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':message_id', $request['id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $this->getMessage($request);
        }
    }
    public function getMessage($request)
    {
        $sql = "SELECT m.id, m.conversation_id, m.message, m.date_added, m.seen_it, m.from, m.direction, m.to, m.type, u.img, u.user_name FROM `messages` m";
        $sql .= " LEFT JOIN `users` u ON m.`from` = u.`id` ";
        $sql .= " WHERE m.`id` = :message_id";
        $sql .= " LIMIT 1 ";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':message_id', $request['id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function showMessagesInfoBetweenMeAndYouApi($conversation_id = 0, $aUserId = 0, $aStart = 0, $aLimit = 0)
    {
        $deleteConversationDate = $this->getDeleteDateByConversation_idAndUser_id($conversation_id, $aUserId);
        $deleteConversationDate = $deleteConversationDate ? $deleteConversationDate : '1970-01-01';

        $sql = "SELECT M.id, M.conversation_id, M.`from`, M.`to`, M.message, M.direction, M.type, M.seen_it, M.date_added FROM  `messages` as M ";
        $sql .= " LEFT JOIN `conversation` C ON (C.`id` = M.`conversation_id`) ";
        $sql .= " WHERE M.`conversation_id` = :conversation_id AND M.`date_added` > :delete_date";

        $sql .= " ORDER BY M.`id` DESC ";
        $sql .= $aLimit ? " LIMIT :start, :limit" : '';

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':delete_date', $deleteConversationDate, PDO::PARAM_STR);
        if ($aLimit) {
            $stmt->bindParam(':start', $aStart, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $aLimit, PDO::PARAM_INT);
        }

        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res = array_reverse($res);
        if ($res) {
            $this->updateMsgSeenIt($conversation_id, $aUserId);
        }
        $res = array_reverse($res);

        return $res;
    }
    public function updateMsgSeenIt($conversation_id = 0, $aUserId = 0)
    {
        $sql = "UPDATE `messages` SET seen_it = '1' ";
        $sql .= " WHERE `to` = :user_id AND  `conversation_id` = :conversation_id";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':user_id', $aUserId, PDO::PARAM_INT);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    }
    public function showMessagesInfo($conversation_id = 0, $aStart = 0, $aLimit = 0)
    {
        $sql = "SELECT uf.user_name , uf.img , M.id, M.conversation_id, M.`from`, M.`to`, M.message, M.direction, M.type, M.seen_it, M.date_added FROM  `messages` as M ";
        $sql .= " LEFT JOIN `conversation` C ON (C.`id` = M.`conversation_id`) ";
        $sql .= " LEFT JOIN `users` uf ON (uf.`id` = M.`from`) ";
        $sql .= " WHERE M.`conversation_id` = :conversation_id ";
        $sql .= " ORDER BY M.`id` DESC ";
        $sql .= $aLimit ? " LIMIT :start, :limit" : '';

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        if ($aLimit) {
            $stmt->bindParam(':start', $aStart, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $aLimit, PDO::PARAM_INT);
        }

        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $res = array_reverse($res);

        return $res;
    }
    public function deleteConversation($conversation_id, $user_id)
    {
        $sql = "UPDATE `conversation` SET ";
        $sql .= ($this->getUser1ByConversationId($conversation_id) == $user_id) ? " date_delete_form_user1 = NOW() " : " date_delete_form_user2 = NOW() ";
        $sql .= " WHERE `id` = :conversation_id ";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
    public function getLiveMessagesApi($conversation_id = 0, $aUserId = 0, $aLimit = 10)
    {
        $deleteConversationDate = $this->getDeleteDateByConversation_idAndUser_id($conversation_id, $aUserId);
        $deleteConversationDate = $deleteConversationDate ? $deleteConversationDate : '1970-01-01';

        $sql = "SELECT M.id, M.conversation_id, M.`from`, M.`to`, M.message, M.direction, M.type, M.seen_it, M.date_added FROM `messages` AS M ";
        $sql .= "WHERE M.`conversation_id` = :conversation_id AND M.`date_added` > :delete_date ";
        $sql .= "ORDER BY M.`id` DESC LIMIT :limit";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindParam(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':delete_date', $deleteConversationDate, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $aLimit, PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($res) {
            $this->updateMsgSeenIt($conversation_id, $aUserId);
        }
        return $res;
    }

    public function trim_text($input, $length, $strip_html = true)
    {
        if ($strip_html) {
            $input = strip_tags($input);
        }

        if (mb_strlen($input, 'UTF-8') <= $length) {
            return $input;
        }
        $last_space = mb_strrpos(mb_substr($input, 0, $length, "utf-8"), ' ', "utf-8");

        $last_space = $last_space ? $last_space : $length;

        $trimmed_text = mb_substr($input, 0, $last_space, "utf-8");
        return $trimmed_text . ' ...';
    }
    public function sendMessageApi($req, $id)
    {
        $sql = "INSERT INTO `messages` (`conversation_id`, `message`, `from`, `to`, `direction`,`date_added`) ";
        $sql .= "VALUES (:conversation_id, :message, :from, :to, :direction, NOW())";

        $stmt = $this->mo->conn->prepare($sql);
        $stmt->bindValue(':conversation_id', $req['conversation_id'], PDO::PARAM_INT);
        $stmt->bindValue(':message', $req['message'], PDO::PARAM_STR);
        $stmt->bindValue(':from', $id, PDO::PARAM_INT);
        $stmt->bindValue(':to', $req['to'], PDO::PARAM_INT);
        $stmt->bindValue(':direction', $req['direction'], PDO::PARAM_STR);

        $stmt->execute();

        return $this->mo->conn->lastInsertId();
    }

}
