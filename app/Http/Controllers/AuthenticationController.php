<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    use GeneralTrait;
    public function signUp(Request $request)
    {     
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return $this->returnError(202, $validator->errors()->first());
        }
        if (!$request->has('name') || !$request->has('email') || !$request->has('password')) {
            return $this->returnError(202, 'name, email and password fields is required');
        }
        $user_image = "";
        if ($request->hasFile('image')) {
            $user_image = $this->saveImage($request->image, 'users');
        }
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'image' => $user_image,
            'password' => Hash::make($request->password),
        ]);
        return $this->returnSuccessMessage('success');
        try {
        } catch (\Exception $e) {
            return $this->returnError(201, $e->getMessage());
        }
    }
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                return $this->returnError(202, $validator->errors()->first());
            }
            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->returnError(202, 'These credentials do not match our records.');
            }

            $token = $user->createToken('my-app-token')->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token
            ];

            return $this->returnData('data', $response);
        } catch (\Exception $e) {
            return $this->returnError(201, $e->getMessage());
        }
    }

    public function getUserInfo($userId)
    {
        try {
            $user = User::select('id', 'name', 'image')->find($userId);
            if (!$user) {
                return $this->returnError(202, 'this user is not exist');
            }
            return $this->returnData('data', $user);
        } catch (\Exception $e) {
            return $this->returnError(201, $e->getMessage());
        }
    }

    public function forgotPassword(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
            ]);
            if ($validator->fails()) {
                return $this->returnError(202, $validator->errors()->first());
            }
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->returnError(202, 'this user is not exist');
            }
            $code = rand(1111, 9999);
            $user->update(['reset_code' => $code]);
            // send email ##################
            return $this->returnSuccessMessage('success');
        }catch (\Exception $e) {
            return $this->returnError(201, $e->getMessage());
        }
    }

    public function resetPassword(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'code' => 'required',
                'password' => 'required|min:6',
            ]);
            if ($validator->fails()) {
                return $this->returnError(202, $validator->errors()->first());
            }
            $user = User::where('reset_code', $request->code)
            ->where('email', $request->email)
            ->first();
            if (!$user) {
                return $this->returnError(202, 'this code is not exist');
            }
            $user->update(['password' => Hash::make($request->password),
            'reset_code' => null
        ]);
            return $this->returnSuccessMessage('success');
        }catch (\Exception $e) {
            return $this->returnError(201, $e->getMessage());
        }
    }
    public function logout()
    {
        try {
            auth()->user()->currentAccessToken()->delete();
            return $this->returnSuccessMessage('success');
        } catch (\Exception $e) {
            return $this->returnError(201, $e->getMessage());
        }
    }
}
