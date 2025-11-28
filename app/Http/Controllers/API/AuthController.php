<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Store;
use Validator;

class AuthController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);
     
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
     
        $input = $request->all();
        $input['id_role'] = $request->role;
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MIT-ECOM')->accessToken;
        $success['name'] =  $user->name;
        $success['role'] = $user->id_role;
   
        return $this->sendResponse($success, 'User register successfully.');
    }
     
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MIT-ECOM')-> accessToken; 
            $success['name'] =  $user->name;
            $success['email'] = $user->email;
            $success['role'] = $user->id_role;
            $store = Store::where('id_seller', $user->id)->first();
            if(!empty($store)){
                $success['store_name'] = $store->name;
                $success['store_desc'] = $store->description;
                $success['store_logo'] = $store->logo;
                $success['store_address'] = $store->address;
                $success['store_status'] = $store->status;
            }
            
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }
}
