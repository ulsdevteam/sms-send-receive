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
foreach ($csvFile as $line) {
	$data[] = str_getcsv($line);
$destination_number = '+'.$data[0][0];
$message = $data[0][1];
//echo $destination_number.' '.$message;
try {
$response = \Telnyx\Message::Create(['from' => $your_telnyx_number, 'to' => $destination_number, 'text' => $message]);
$lastResponse = $response->getLastResponse();
} catch (Exception $e) {
    error_log($e->getCode().' '.$e->getMessage());
}
    if ($lastResponse && $lastResponse->code === 200 && isset($lastResponse->json) && isset($lastResponse->json['received_at'])) {
//echo $decoded->{'data'}->{'record_type'};
//var_dump($response);
        echo $lastResponse->json['received_at'];
        $success++;
    } else {
        // TODO: describe these conditions
        error_log(join(',', array($lastResponse, $lastResponse->code === 200, isset($lastResponse->json), isset($lastResponse->json['received_at']))));
	//to do: if API returns fail
	//cat error string to main failure log
	$failureLog = fopen("error/failedToSend.csv", 'a+');
	fwrite($failureLog,$line); 
	$errorCount++;
}
}
if ($errorCount>0){
//	echo 'Failed to send '. $errorCount.' message(s)'.PHP_EOL;
	
}
?>
