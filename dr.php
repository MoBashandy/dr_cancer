<?php
require_once 'init.php';

if (isset($user_token) && ($user_init)) {
    if ($_GET['action'] == 'Get_Dr' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $Data['data'] = $DR->Get_Dr($user_init['lat'],$user_init['lon'],$Req);
        $Data['success'] = true;
    }
}


echo json_encode($Data);