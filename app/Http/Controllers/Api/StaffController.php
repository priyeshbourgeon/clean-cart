<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use DB;

class StaffController extends Controller
{
    public function users(Request $request) 
    {
        $name = $request->name;
        $users = User::where('name','like',$name.'%')->get();
        return response()->json([
            'status' => true,
            'data' => $users,
        ]);
    }

	public function roles()
    {
        $roles = Role::where('slug', '!=', 'super-admin')->get();

        return response()->json([
            'status' => true,
            'data' => $roles
        ]);
    }

    public function storeUser(UserRequest $request){
    	$authUser = auth()->user();
    
    	if($authUser->role->slug != 'manager') {
        	return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403);
        }
    
        DB::beginTransaction();
        try {
        	// dd((int)$request->role_id);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
            	'role_id' => (int)$request->role_id,
            	'password' => Hash::make($request->password),
            	'punnyam_code'=> $authUser->punnyam_code
            ]);
        
        	$user->role_id = (int)$request->role_id;
            $user->save();
        
            DB::commit();
            return response()->json([
                'status' => true,
                'data' => $user,
                'message' => 'User Created!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => 'Something went wrong!'
            ]);  
        }
        
    }
}
