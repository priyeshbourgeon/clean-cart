<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{


    public function sendOTP($otp,$mobile){
    
        $message =urlencode("Your otp for validating the mobille number is $otp for details please contact 0000000000 REGARDS PARKIN.GATEWAY SECURITY");
        
        $url = "http://sms.bourgeoninnovations.com/SMS_API/sendsms.php?apikey=47cd31b3-9d7a-11ee-a4f5-e29d2b69142c&mobile=$mobile&sendername=GTWYSG&message=$message&routetype=1&tid=1707170289369798525";

         // Create a Guzzle client
        $client = new Client();

        $response = $client->get($url);

        $resp = $response->getBody()->getContents();
    	
        return $resp;
    }

public function signup(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'mobile' => 'required|unique:customers,mobile',
        'password' => 'required|confirmed',
    ]);


    Session::put('customer_signup', [
        'name' => $request->name,
        'mobile' => $request->mobile,
        'password' => Hash::make($request->password),
    ]);

    $otp = rand(1000, 9999); 

    Session::put('otp', $otp);

    $response = $this->sendOTP($otp, $request->mobile);

    return response()->json(['message' => 'OTP sent successfully', 'otp_response' => $response]);
}

public function verifyOtp(Request $request)
{
    $otpSession = Session::get('otp');  

    if ($request->otp == $otpSession) {
        
        $customerData = Session::get('customer_signup');

        
        $customer = Customer::create([
            'name' => $customerData['name'],
            'mobile' => $customerData['mobile'],
            'password' => $customerData['password'],
        ]);

        $customer->otp_verified = 1;
        $customer->save();

        Session::forget('customer_signup');
        Session::forget('otp');

        return response()->json(['message' => 'OTP verified and account created successfully']);
    }

    return response()->json(['message' => 'Invalid OTP'], 400);
}


    public function signin(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
            'password' => 'required',
        ]);

        $customer = Customer::where('mobile', $request->mobile)->first();

        if ($customer && Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Login successful']);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}
