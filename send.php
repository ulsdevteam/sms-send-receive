<?php
require_once '../vendor/autoload.php';
include '../config.php';
if (isset($_SERVER['SERVER_NAME'])) {
    error_log('Can not call this from the web');
    exit;
}

\Telnyx\Telnyx::setApiKey(TELNYX_API_KEY);

$your_telnyx_number = TELNYX_PHONE_NUM;
//loop and send all the new letters here
$csvFile = file('/var/local/patron/sms/pending/messages.csv');
$data = [];
$errorCount=0;

function askTelnyxToSendSMS($your_telnyx_number,$destination_number,$message){
	$response = \Telnyx\Message::Create(['from' => $your_telnyx_number, 'to' => $destination_number, 'text' => $message]);
	$lastResponse = $response->getLastResponse();
	if ($lastResponse && $lastResponse->code === 200 && isset($lastResponse->json) && isset($lastResponse->json['status']) && $lastResponse->json['status']=='delivered') {
		//echo $decoded->{'data'}->{'record_type'};
		//var_dump($response);
		//echo $lastResponse->json['received_at'];
		var_dump($response);
		return true;
	} else {
		var_dump($response);
		throw new Exception("Failed to send. HTTP: $lastResponse->code. $destination_number. $message");
	}
}
//attempt all the sends and log any errors
foreach ($csvFile as $line) {
	$data[] = str_getcsv($line);
	$destination_number = '+'.$data[0][0];
	$message = $data[0][1];
	//echo $destination_number.' '.$message;
	try {
		askTelnyxToSendSMS($your_telnyx_number,$destination_number,$message);
		$success++;
	} catch (Exception $e) {
		$failureLog = fopen("error/failedToSend.csv", 'a+');
		fwrite($failureLog,$e->getMessage()); 
		$errorCount++;
	}
}
if ($errorCount>0){
	//perhaps send an email alert
	//echo 'Failed to send '. $errorCount.' message(s)'.PHP_EOL;
}
?>
