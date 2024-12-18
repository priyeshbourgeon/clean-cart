<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'punnyam_code' => 'required',
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'punnyam_code' => $request->punnyam_code,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required',
            ]);
        
        	

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first(); 
        
        // Role checking for Supervisor and Staff
        if ($user->role->slug == 'supervisor') {
            return response()->json([
                'status'   => true,
                'message'  => 'Supervisor Logged In Successfully',
                'user'     => [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'role_id' => $user->role_id,
    'role' => $user->role->name // Assuming 'role' is a relationship, you can use $user->role->name or similar
],
                'token'    => $user->createToken("SUPERVISOR TOKEN")->plainTextToken,
            ], 200);

        } elseif ($user->role->slug == 'staff') {
            return response()->json([
                'status'   => true,
                'message'  => 'Staff Logged In Successfully',
                'user'     => [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'role_id' => $user->role_id,
    'role' => $user->role->name // Assuming 'role' is a relationship, you can use $user->role->name or similar
],
                'token'    => $user->createToken("STAFF TOKEN")->plainTextToken,
            ], 200);
        } elseif ($user->role->slug == 'manager') {
            return response()->json([
                'status'   => true,
                'message'  => 'Manager Logged In Successfully',
                'user'     => [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'role_id' => $user->role_id,
    'role' => $user->role->name // Assuming 'role' is a relationship, you can use $user->role->name or similar
],
                'token'    => $user->createToken("MANAGER TOKEN")->plainTextToken,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403); // Forbidden
        }
            return response()->json([
                'status'   => true,
                'message'  => 'User Logged In Successfully',
                'token'    => $user->createToken("API TOKEN")->plainTextToken,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
                'status' => true,
                'message' => 'User Logged Out'
        ], 200);
    }

	public function deleteAccount(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->status = 0;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User marked as deleted successfully'
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}





}
