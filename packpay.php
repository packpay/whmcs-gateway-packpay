<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function redirect($url) {
    if(!headers_sent()) {
        header('Location: ' . $url);
        exit;
    }
}

function getToken($refreshToken, $clientID, $secretID) {
    try {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://dashboard.packpay.ir/oauth/token?grant_type=refresh_token&refresh_token=" . $refreshToken);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $clientID . ':' . $secretID);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }
    catch(Exception $e) {
        return false;
    }

    $result = json_decode($result);
    return $result->access_token;
}

$Amount = intval($_POST['amount']);
$systemUrl = $_POST['systemurl'];

if($_POST['currencies'] == 'Toman') {
    $Amount = round($Amount * 10);
}

$CallbackURL = $systemUrl . 'modules/gateways/callback/packpay.php?invoiceid=' . $_POST['invoiceid'];
try {
    $token = getToken($_POST["refresh_token"], $_POST["client_id"], $_POST["secret_id"]);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://dashboard.packpay.ir/developers/bank/api/v1/purchase");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'accept: application/json',
        'authorization: Bearer ' . $token
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        "amount=" . $Amount .
        "&payer_id=" . $_POST['invoiceid'] .
        "&payer_name=" . $_POST['email'] .
        "&callback_url=" . $CallbackURL);

    $result = curl_exec($ch);
    $result = json_decode($result);
}
catch(Exception $e) {
    echo '<h2>وقوع وقفه!</h2>';
    echo $e->getMessage();
}

if(assert($result->reference_code)) {
    $url = "https://dashboard.packpay.ir/bank/purchase/send?RefId=". $result->reference_code;
    redirect($url);
}
else {
    echo "<h2>وقوع خطا در ارتباط!</h2>"
        . 'کد خطا' . $result->Status;
}

?>
