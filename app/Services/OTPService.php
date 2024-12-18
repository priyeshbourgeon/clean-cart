<?php

namespace App\Services;

use GuzzleHttp\Client;

class OTPService
{
    // Function to send OTP
    public function sendOTP($otp, $mobile)
    {
        $message = urlencode("Your otp for validating the mobile number is $otp. For details, please contact 0000000000. REGARDS PARKIN.GATEWAY SECURITY");
        
        $url = "http://sms.bourgeoninnovations.com/SMS_API/sendsms.php?apikey=47cd31b3-9d7a-11ee-a4f5-e29d2b69142c&mobile=$mobile&sendername=GTWYSG&message=$message&routetype=1&tid=1707170289369798525";

        $client = new Client();

        $response = $client->get($url);

        $resp = $response->getBody()->getContents();

        return $resp;
    }
}
