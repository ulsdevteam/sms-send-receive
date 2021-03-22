<?php
require_once '../vendor/autoload.php';
include '../config.php';
include 'telnyxMessageCreate.php';
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

//attempt all the sends and log any errors
foreach ($csvFile as $line) {
	//put the csv line into an array
	$data[] = str_getcsv($line);
	//array will have two elements per index, the phone number and the message
	$destination_number = '+'.$data[0][0];
	$message = $data[0][1];
	//echo $destination_number.' '.$message;
	try {
		telnyxMessageCreate($your_telnyx_number,$destination_number,$message);
	} catch (Exception $e) {
		fwrite(STDERR, "$e->getMessage()");
		$failureLog = fopen("/var/local/patron/sms/error/failedToSend.csv", 'a+');
		fwrite($failureLog,$line); 
	}
}
?>
