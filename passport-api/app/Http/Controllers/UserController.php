<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name"      =>  "required|min:3|max:30",
            "email"     =>  "required|email",
            "password"  =>  "required|min:8",
        ]);

        if($validator->fails())
        {
            return response()->json([
                "success"   =>  false,
                "errors"    => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try
        {
            $user = User::create([
                "name"      =>  $request->name,
                "email"     =>  $request->email,
                "password"  =>  Hash::make($request->password),
            ]);

            $access_token = $user->createToken("access_token")->accessToken;

            DB::commit();
            return response()->json([
                "success"       => true,
                "message"       =>  "User created successfully",
                "access_token"  =>  $access_token,
                "user"          =>  $user,
            ], 200);
        }
        catch(\Exception $e)
        {
            DB::rollBack();
            return response()->json([
                "errors" => $e->getMessage(),
            ]);
        }
    }


    public function login(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            "email"     =>  "required|email",
            "password"  =>  "required",
        ]);

        if($validator->fails())
        {
            return response()->json([
                "success"   =>  false,
                "errors"    => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try 
        {
            $user = User::where("email", $request->email)->first();
            if(empty($user))
            {
                return response()->json([
                    "success"   =>  false,
                    "message"   =>  "User does not exist",
                ]);
            }

            $credentials = [
                "email"     =>  $request->email,
                "password"  =>  $request->password,
            ];

            if(!Auth::attempt($credentials))
            {
                return response()->json([
                    "success"   =>  false,
                    "message"   =>  "Incorrect email or password",
                ], 401);
            }

            Auth::login($user);
            $access_token = $user->createToken("access_token")->accessToken;
            DB::commit();

            return response()->json([
                "success"       =>  true,
                "access_token"  =>  $access_token,
                "user"          =>  $user,
            ], 200);

        } 
        catch (\Exception $e) 
        {
            DB::rollBack();
            return response()->json([
                "success"   =>  false,
                "message"   =>  $e->getMessage(),
            ], 500);
        }
    }
}
