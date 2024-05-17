<?php

require_once 'init.php';
if ($_GET['action'] == 'checkIfEmailExist' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (filter_var($Req['email'], FILTER_VALIDATE_EMAIL)) {
        $userInfo = $USAObj->checkIfEmailExist(0, $Req['email']);
        if (!empty($userInfo)) {
            $Data['success'] = true;
            $Data['message'] = 'Email_Exist';
        } else {
            if ($Req['email']) {
                $code = $USAObj->generatePasswordResetToken();
                if ($code) {
                    $mo_touch = $USAObj->checkIfEmailExist(1, $Req['email']);
                    $stored = $USAObj->storePasswordResetToken($Req['email'], $code);
                    if ($stored) {
                        $emailSubject = "Verification Email";
                        $emailBody = "Hello, you requested to verify your email. Here is your verification code: $code";
                        $fromEmail = $Config['site_email'];

                        if (function_exists('mail')) {
                            $mailed = $mailer->sendEmail($Req['email'], $emailSubject, $emailBody, '<' . $fromEmail . '>', $fromEmail);

                            if ($mailed) {
                                $Data['success'] = true;
                                $Data['message'] = 'sended_email';
                            } else {
                                $Data['success'] = false;
                                $Data['message'] = 'failed_send';
                            }
                        } else {
                            $Data['success'] = false;
                            $Data['message'] = 'failed_mail';
                        }
                    } else {
                        $Data['success'] = false;
                        $Data['message'] = 'failed_store';
                    }
                } else {
                    $Data['success'] = false;
                    $Data['message'] = 'failed_gen';
                }
            } else {
                $Data['success'] = false;
                $Data['message'] = 'Email_not_provided';
            }

        }
    } else {
        $Data['error'] = "Invalid_email_format";
    }
} elseif ($_GET['action'] == 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $userInfo = $USAObj->checkIfEmailExist(0, $Req['email']);
    if (!empty($userInfo)) {
        $hash = sha1($Req['password']);
        $correct = $USAObj->checkPassword($Req['email'], $hash);
        if ($correct) {
            $USAObj->updateLastLoging($userInfo['id']);
            $signInData = $USAObj->getUserDetailsInfo($userInfo['id']);

            $Data['data'] = $signInData;
            $Data['success'] = true;
        } else {
            $Data['success'] = false;
            $Data['error'] = 'Incorrect_Password';
        }
    } else {
        $Data['success'] = false;
        $Data['error'] = 'Email_Not_Exist';
    }
} elseif ($_GET['action'] == 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $mo_touch = $USAObj->checkIfEmailExist(1, $Req['email']);
    $email_info = $USAObj->checkIfEmailExist(0, $Req['email']);

    if (strlen($Req['password']) < 6) {
        $Data['error'] = 'Password Too Short';
        $Data['success'] = false;
    } elseif ($email_info) {
        $Data['error'] = 'Email_exists';
        $Data['success'] = false;
    } elseif (empty($Req['name']) || strlen($Req['name']) < 4) {
        $Data['error'] = 'the_name_is_fewer_than_4_characters';
        $Data['success'] = false;
    } else {
            $res = $USAObj->register(sha1($Req['password']), $Req);
            if ($res) {

                $Data['success'] = true;
                $userInfo = $USAObj->checkIfEmailExist(0, $Req['email']);
                $make_auth = $USAObj->makeAuthCode($userInfo['id']);
                $signInData = $USAObj->getUserDetailsInfo($userInfo['id']);
                $Data['data'] = $signInData;
                $mo_touch = $USAObj->checkIfEmailExist(1, $Req['email']);

            } else {
                $Data['success'] = false;
            }

    }
} elseif ($_GET['action'] == 'check_code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($Req['code'])) {
        $Data['error'] = 'Invalid_code';
        $Data['success'] = false;
    } else {
        $userInfo = $USAObj->check_code($Req['email'], $Req['code']);
        if (!$userInfo) {
            $Data['error'] = 'Invalid_code';
            $Data['success'] = false;
        } else {
            $Data['success'] = true;
        }
    }
} elseif ($_GET['action'] == 'forgotPassword' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInfo = $USAObj->checkIfEmailExist(0, $Req['email']);

    // Check if the provided email is in a valid format
    if ($userInfo) {
        if (!filter_var($Req['email'], FILTER_VALIDATE_EMAIL)) {
            $Data['success'] = false;
            $Data['error'] = 'Invalid_Email';
        } else {

            $code = $USAObj->generatePasswordResetToken();

            // Store the password reset token in the database
            $stored = $USAObj->storePasswordResetToken($Req['email'], $code);

            if ($stored) {
                // Send an email with the reset password link
                $emailSubject = "Reset Password";
                $emailBody = "Hello you Asked to Change your Password Here Is your Password Reset : $code";
                $fromEmail = $Config['site_email'];

                // Check if the mail function exists
                if (function_exists('mail')) {
                    // Send the email using the mail function
                    $mailed = $mailer->sendEmail($Req['email'], $emailSubject, $emailBody, '<' . $fromEmail . '>', $fromEmail);

                    if ($mailed) {
                        $Data['message'] = 'Password_reset_Pass_sent_to_your_email';
                        $Data['success'] = true;
                    } else {
                        $Data['error'] = 'Failed_to_send_email_Please_try_again_later.';
                        $Data['success'] = false;
                    }
                } else {
                    $Data['error'] = 'Mail_function_is_not_available_on_this_server.';
                    $Data['success'] = false;
                }
            } else {
                $Data['error'] = 'Failed_to_store_password_reset_code.';
                $Data['success'] = false;
            }
        }
    } else {
        $Data['success'] = false;
        $Data['error'] = 'Email_not_found';
    }
} elseif ($_GET['action'] == 'updatePassword' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInfo = $USAObj->check_code($Req['email'], $Req['code']);

    // Check if the provided email and token are valid
    if (empty($userInfo)) {
        $Data['error'] = 'Invalid_Email_or_Token';
    } elseif (!filter_var($Req['email'], FILTER_VALIDATE_EMAIL)) {
        $Data['error'] = 'Invalid_Email';
    } elseif (strlen($Req['new_password']) < 6) {
        $Data['error'] = 'Password_Too_Short';
    } else {
        $mo_touch = $USAObj->checkIfEmailExist(1, $Req['email']);
        $userInfo = $USAObj->checkIfEmailExist(0, $Req['email']);
        if (empty($userInfo)) {
            $Data['error'] = 'Email_not_found';
        } else {
            $hashedPassword = sha1($Req['new_password']);
            $passwordUpdated = $USAObj->updatePassword($Req['email'], $hashedPassword);

            if ($passwordUpdated) {
                $Data['message'] = 'Password_updated_successfully';
                $Data['success'] = true;
            } else {
                $Data['error'] = 'Failed_to_update_password_Please_try_again_later.';
                $Data['success'] = false;
            }
        }
    }

}
echo json_encode($Data);
