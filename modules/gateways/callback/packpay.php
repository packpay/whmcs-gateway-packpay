<?php
/*
 *::: www.vahabonline.ir
 *::: myvahab@gmail.com
 */
# Required File Includes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(file_exists('../../../init.php')) {
    require('../../../init.php');
}
else {
    require("../../../dbconnect.php");
}
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

function getToken($refreshToken, $clientID, $secretID) {
    try {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://dashboard.packpay.ir/oauth/token?grant_type=refresh_token&refresh_token=" . $refreshToken);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $clientID . ':' . $secretID);

        $result = curl_exec($ch);
        if(curl_errno($ch)) {
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

function getPayInfo($reference_code, $payer_id, $token) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,
        'https://dashboard.packpay.ir/developers/bank/api/v1/purchase?reference_code=' . $reference_code . '&payer_id=' . $payer_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'accept: application/json',
        'authorization: Bearer ' . $token
    ));

    $result = curl_exec($ch);
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $result = json_decode($result);
    return $result;
}

$gatewaymodule = 'packpay';

$GATEWAY = getGatewayVariables($gatewaymodule);
if(!$GATEWAY['type']) die('Module Not Activated');

$token = getToken($GATEWAY["refresh_token"], $GATEWAY["client_id"], $GATEWAY["secret_id"]);
$payInfo = getPayInfo($_GET['reference_code'], $_GET['invoiceid'], $token);
$Amount = $payInfo->data[0]->amount;
$invoiceid = $_GET['invoiceid'];
$transid = $_GET['reference_code'];
$invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY['name']); # Checks invoice ID is a valid invoice number or ends processing

if($payInfo->data[0]->transactionStatus == "موفق") {
    try {
        checkCbTransID($transid);
    }
    catch(Exception $e) {
        echo '<h2>وقوع وقفه!</h2>';
        print_r($e);
    }
}

if($GATEWAY['Currencies'] == 'Toman') {
    $Amount = $Amount / 10;
}

if($payInfo->data[0]->transactionStatus == "موفق") {
    addInvoicePayment($invoiceid, $transid, $Amount, 0, $gatewaymodule);
    logTransaction(
        $GATEWAY['name'],
        array('Get' => $_GET, 'Data' => [
            "invoiceid" => $invoiceid,
            "amount" => $Amount,
            "reference_code" => $transid,
            "status" => $payInfo->data[0]->transactionStatus,
        ]),
        'Successful'
    );
}
else {
    logTransaction(
        $GATEWAY['name'],
        array(
            'Get' => $_GET,
            'Data' => [
                "invoiceid" => $invoiceid,
                "amount" => $Amount,
                "reference_code" => $transid,
                "status" => $payInfo->data[0]->transactionStatus,
            ],
        ),
        'Unsuccessful'
    );
}
Header('Location: ' . $CONFIG['SystemURL'] . '/clientarea.php?action=invoices');

?>
