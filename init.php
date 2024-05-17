<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('memory_limit', '-1');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, User-Token, lang, App-Version, Usertoken, Authorization, X-Requested-With, access-control-allow-origin, Secretapi');

$postdata = file_get_contents("php://input");
$Req = json_decode($postdata, true);
if (!function_exists('apache_request_headers')) {
    function apache_request_headers()
    {
        $return = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $return[$key] = $value;
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }
}

$apache_request = apache_request_headers();

if (is_numeric(strpos($apache_request['User-Agent'], "python-requests")) || is_numeric(strpos($apache_request['Content-Type'], "application/x-www-form-urlencoded")) || is_numeric(strpos($apache_request['User-Agent'], "PostmanRuntime"))) {
    echo json_encode(["status" => 401, "message" => "Unauthorized Request"]);
    exit();
}

require_once 'class/mo.php';
require_once 'class/class.user_auth.php';
require_once 'class/class.dr.php';
require_once 'class/class.RMM.php';
require_once 'class/class.messages.php';

$lang = apache_request_headers()["Lang"];
define('LANGUAGE', $lang);

$app_version = apache_request_headers()["App-Version"];
define('APP_VERSION', $app_version);

$user_token = apache_request_headers()["User-Token"];
$user_init = $USAObj->getUserDataByAuth($user_token);

if ($_GET['action'] != 'addConversation') {
    $stopWords = [
        '/||/',
        '/&&/',
        '/JOIN/',
        '/join/',
        '/UNION/',
        '/SQL/',
        '/-- -/',
        '/DATABASE/',
        '/OR/',
        '/NOR/',
    ];

    $ReqST = preg_replace($stopWords, '', json_encode($Req));
    $_GESr = preg_replace($stopWords, '', json_encode($_GET));
    $ReqST = str_replace("\'", "'", $ReqST);
    $_GESr = str_replace("\'", "'", $_GESr);
    $Req = json_decode($ReqST, true);
    $_GET = json_decode($_GESr, true);
}
