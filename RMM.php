<?php
require_once 'init.php';

if (isset($user_token) && ($user_init)) {
    if ($_GET['action'] == 'Add_Mid' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $Data['data'] = $RMM->Add_Mid($user_init['id'],$Req);
        $Data['success'] = true;
    }elseif ($_GET['action'] == 'Get_Rem_Mid' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $Data['data'] = $RMM->Get_Rem_Mid($user_init['id']);
        $Data['success'] = true;
    }
}


echo json_encode($Data);