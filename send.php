<?php
require_once '../vendor/autoload.php';
include '../config.php';
if (isset($_SERVER['SERVER_NAME'])) {
    error_log('Can not call this from the web');
    exit;
}

\Telnyx\Telnyx::setApiKey(TELNYX_API_KEY);

$your_telnyx_number = TELNYX_PHONE_NUM;
//letters from alma stored here:
$csvFile = file('/var/local/patron/sms/pending/messages.csv');
$data = [];
$errorCount=0;

/**
 * Calls the Telnyx Message Create method and interprets the response
 * @param string $your_telnyx_number Telnyx number that will send the messages.
 * @param string $destination_number Recipient's phone number from messages.csv.
 * @param string $message Content of the outgoing SMS from messages.csv.
 * @throws Exception if the response object includes errors
 * @return bool
 */
function askTelnyxToSendSMS($your_telnyx_number,$destination_number,$message){
	$response = \Telnyx\Message::Create(['from' => $your_telnyx_number, 'to' => $destination_number, 'text' => $message]);
	$lastResponse = $response->getLastResponse();
	if ($lastResponse && $lastResponse->code === 200 && isset($lastResponse->json) && $lastResponse->json['errors'] !== false) {
		//echo $decoded->{'data'}->{'record_type'};
		//var_dump($response);
		//echo $lastResponse->json['received_at'];
		echo 'Success!'.PHP_EOL;
		print_r($response);
		return true;
	} else {
		print_r($response);
		throw new Exception('"'.$destination_number.'"'.'"'. $message.'"');
	}
}
//attempt all the sends and log any errors
foreach ($csvFile as $line) {
	//put the csv line into an array
	$data[] = str_getcsv($line);
	//array will have two elements per index, the phone number and the message
	$destination_number = '+'.$data[0][0];
	$message = $data[0][1];
	//echo $destination_number.' '.$message;
	try {
		askTelnyxToSendSMS($your_telnyx_number,$destination_number,$message);
	} catch (Exception $e) {
		$failureLog = fopen("/var/local/patron/sms/error/failedToSend.csv", 'a+');
		fwrite($failureLog,$e->getMessage()); 
		$errorCount++;
	}
}
if ($errorCount>0){
	//perhaps send an email alert
	//echo 'Failed to send '. $errorCount.' message(s)'.PHP_EOL;
}
?>
