<?php
require_once 'init.php';
if (isset($user_token) && ($user_init)) {
    if ($_GET['action'] == 'addConversation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userStatus = $message->checkIfIhaveAlreadyConversation($user_init['id'], $Req['user_id']);
        if (!$userStatus) {
            $data = $message->addConversationApi($user_init['id'], $Req['user_id']);
            $Data['success'] = true;
            $Data['data'] = $data;
        } else {
            $Data['success'] = false;
            $Data['error'] = 'alreadyHaveConversation';
        }
    } elseif ($_GET['action'] == 'getAllConversation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $message->getAllConversationByUserIdApi($user_init['id'], 0);
        if ($data) {
            $Data['success'] = true;
            $Data['data'] = $data;
        } else {
            $Data['success'] = false;
            $Data['error'] = 'noContent';
        }
    } elseif ($_GET['action'] == 'messageHasSeen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $message->messageHasSeen($Req);
        if ($data) {
            $Data['data'] = $data;
            $Data['success'] = true;
        } else {
            $Data['error'] = 'noContent';
            $Data['success'] = false;
        }
    } elseif ($_GET['action'] == 'getConversationMessages' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $message->showMessagesInfoBetweenMeAndYouApi($Req['conversation_id'], $user_init['id'], $Req['start'], $Req['limit']);

        if ($data) {
            $Data['data'] = $data;
            $Data['success'] = true;
        } else {
            $Data['error'] = 'noContent';
            $Data['success'] = false;
        }
    } elseif ($_GET['action'] == 'getConversationMessagesAll' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $message->showMessagesInfo($Req['conversation_id'], $Req['start'], $Req['limit']);
        if ($data) {
            $Data['data'] = $data;
            $Data['success'] = true;
        } else {
            $Data['error'] = 'noContent';
            $Data['success'] = false;
        }
    } elseif ($_GET['action'] == 'deleteConversation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $message->deleteConversation($Req['conversation_id'], $user_init['id']);
        if ($data) {
            $Data['data'] = $data;
            $Data['success'] = true;
        } else {
            $Data['error'] = true;
            $Data['success'] = false;
        }
    } elseif ($_GET['action'] == 'getLiveMessages' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $message->getLiveMessagesApi($Req['conversation_id'], $user_init['id'], $Req['limit']);
        if ($data) {
            $Data['data'] = $data;
            $Data['success'] = true;
        } else {
            $Data['error'] = 'noContent';
            $Data['success'] = false;
        }
    } elseif ($_GET['action'] == 'getUnreadMsgCount' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $data = $message->getUnreadMessageCountApi($Req['user_id']);
        if ($data) {
            $Data['data'] = $data;
        } else {
            $Data['error'] = 'noContent';
        }
    } elseif ($_GET['action'] == 'sendMessage' && $_SERVER['REQUEST_METHOD'] === 'POST') {

        $data = $message->sendMessageApi($Req, $user_init['id']);
        if ($data) {
            $Data['data'] = $data;
            $Data['success'] = true;
        } else {
            $Data['error'] = 'noContent';
            $Data['success'] = false;
        }
    }
}

echo json_encode($Data);
