<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Booking;
use App\Models\WashType;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    public function bookSingleWash(Request $request)
    {
        $washType = WashType::where('type_name', $request->car_type)->first();
        
        if ($washType->quantity >= $request->wash_date) {
            $booking = Booking::create([
                'customer_id' => $request->customer_id,
                'franchise_id' => $request->franchise_id,
                'car_type' => $request->car_type,
                'car_number' => $request->car_number,
                'wash_type' => 'single',
                'booking_date' => $request->wash_date,
                'booking_time' => $request->wash_time,
                'address' => $request->address,
            ]);
            return response()->json(['message' => 'Booking confirmed']);
        }
        
        return response()->json(['message' => 'Booking unavailable'], 400);
    }

    public function myBookings(Request $request)
    {
        $bookings = Booking::where('customer_id', $request->customer_id)->get();
        return response()->json($bookings);
    }
}
