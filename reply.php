<?php
require_once '../vendor/autoload.php';
include '../config.php';
include 'telnyxMessageCreate.php';
//require_once '../bootstrap.php';
\Telnyx\Telnyx::setApiKey(TELNYX_API_KEY);

// Please fetch the public key from: https://portal.telnyx.com/#/app/account/public-key
\Telnyx\Telnyx::setPublicKey(TELNYX_PUBLIC_KEY);
$webhook_event = null;
try {
    // Validate the webhook against the public key and retrieve the $webhook_event object
    $webhook_event = \Telnyx\Webhook::constructFromRequest();
	#constructFromRequest can return two possible errors:
    } catch(\UnexpectedValueException $e) { // Invalid payload
        // Output error message
        error_log('Invalid payload'); 
        // Send status code to signal that the webhook was NOT successfully received
        http_response_code(400);
        exit();
    } catch(\Telnyx\Exception\SignatureVerificationException $e) { // Invalid signature
        // Output error message
        error_log('Invalid signature'); 
        // Send status code to signal that the webhook was NOT successfully received
        http_response_code(400);
        exit();
	}   
	
// Now you can work with the $webhook_event object
// Send status code to signal that the webhook was successfully received
http_response_code(200);

$your_telnyx_number = TELNYX_PHONE_NUM;

$message = strtolower($webhook_event["data"]["payload"]["text"]);
$reply = 'ask a librarian';

//test
/*
    if ($message === "info"){
        error_log("Stop: " . print_r($webhook_event["data"]["payload"]["from"]["phone_number"], true));
	}
*/


//Reply conditions	
//https://support.telnyx.com/en/articles/1270091-sms-opt-out-keywords-and-stop-words
$stopwords=array('stop', 'stopall', 'stop all', 'unsubscribe', 'cancel', 'end', 'quit');
$startwords=array('start','unstop');
if (in_array($message,$stopwords)){
	//handle the unsubscribe
	error_log('telnyx unsubscribe');
}  
else if (in_array($message,$startwords)){
	//handle subscription
	error_log('telnyx subscribe');
}

else{
	//source#, destination#, message
	try {
		telnyxMessageCreate($your_telnyx_number,$webhook_event["data"]["payload"]["from"]["phone_number"],$reply);        
    } catch (Exception $e) {
    	error_log('Telnyx reply failed');
	}   
}
?>
