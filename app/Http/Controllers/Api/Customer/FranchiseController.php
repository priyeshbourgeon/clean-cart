<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use Illuminate\Http\Request;

class FranchiseController extends Controller
{
    public function getFranchises(Request $request)
    {
        $location = $request->input('location');
        
        // Search for franchises matching the location
        $franchises = Franchise::where('location', 'LIKE', '%'.$location.'%')->get();
        
        // Check if any franchises were found
        if ($franchises->isEmpty()) {
            return response()->json(['message' => 'No franchises found for the specified location'], 404);
        }
        
        // Return the list of franchises if found
        return response()->json($franchises);
    }
}
