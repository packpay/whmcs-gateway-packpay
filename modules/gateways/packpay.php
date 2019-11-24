<?php
    /*
     *::: www.vahabonline.ir
     *::: myvahab@gmail.com
     */
function packpay_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"پک پی"),
     "refresh_token" => array("FriendlyName" => "Refresh Token", "Type" => "text", "Size" => "50", ),
     "client_id" => array("FriendlyName" => "Client ID", "Type" => "text", "Size" => "50", ),
     "secret_id" => array("FriendlyName" => "Secret ID", "Type" => "text", "Size" => "50", ),
     "Currencies" => array("FriendlyName" => "Currencies", "Type" => "dropdown", "Options" => "Rial,Toman", ),
     );
	return $configarray;
}

function packpay_link($params) {

	# Gateway Specific Variables
	$refresh_token = $params['refresh_token'];
    $currencies = $params['Currencies'];
	$client_id = $params['client_id'];
	$secret_id = $params['secret_id'];

	# Invoice Variables
	$invoiceid = $params['invoiceid'];
	$description = $params["description"];
    $amount = $params['amount']; # Format: ##.##
    $currency = $params['currency']; # Currency Code

	# Client Variables
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	$email = $params['clientdetails']['email'];
	$address1 = $params['clientdetails']['address1'];
	$address2 = $params['clientdetails']['address2'];
	$city = $params['clientdetails']['city'];
	$state = $params['clientdetails']['state'];
	$postcode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$phone = $params['clientdetails']['phonenumber'];

	# System Variables
	$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	$currency = $params['currency'];

	# Enter your code submit to the gateway...

	$code = '
    <form method="post" action="./packpay.php">
        <input type="hidden" name="refresh_token" value="'. $refresh_token .'" />
        <input type="hidden" name="client_id" value="'. $client_id .'" />
        <input type="hidden" name="secret_id" value="'. $secret_id .'" />
        <input type="hidden" name="invoiceid" value="'. $invoiceid .'" />
        <input type="hidden" name="amount" value="'. $amount .'" />
        <input type="hidden" name="currencies" value="'. $currencies .'" />
        <input type="hidden" name="systemurl" value="'. $systemurl .'" />
		<input type="hidden" name="email" value="'. $email .'" />
		<input type="hidden" name="cellnum" value="'. $phone .'" />
        <input type="submit" name="pay" value=" پرداخت " />
    </form>
    ';

	return $code;
}
?>
