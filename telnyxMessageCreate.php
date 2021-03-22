<?php
/**
 * Calls the Telnyx Message Create method and interprets the response
 * @param string $your_telnyx_number Telnyx number that will send the messages.
 * @param string $destination_number Recipient's phone number from messages.csv.
 * @param string $message Content of the outgoing SMS from messages.csv.
 * @throws Exception if the response object includes errors
 * @return bool
 */
function telnyxMessageCreate($your_telnyx_number,$destination_number,$message){
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
        throw new Exception('"'.$getLastResponse.'"');
    }   
}
?>
