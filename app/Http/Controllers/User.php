<?php

namespace App\Http\Controllers;

use App\Models\User as ModelsUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class User extends Controller
{
    //Register new account
    public function create(Request $request)
    {
        //Validate the incoming data
        [
            "fullname" => $fullname,
            "whatsapp" => $whatsapp,
            "password" => $password,
            "gender" => $gender,
            "dob" => $dob,
        ] = $request->validate([
            "fullname" => "required",
            "whatsapp" => "required|numeric|unique:users,whatsapp",
            "password" => "required",
            "gender" => ["required", Rule::in(["male", "female"])],
            "dob" => ["required", "date:d-m-Y)"]
        ]);

        //hashing the password using argon
        $password = password_hash($password, PASSWORD_ARGON2I);

        //Create user model
        $user = new ModelsUser();
        $user->fullname = $fullname;
        $user->whatsapp = $whatsapp;
        $user->password = $password;
        $user->gender = $gender;
        $user->dob = $dob;

        //Saving user model
        $user->save();

        //return the response with created token
        return $user->toArray() + ["token" => ($user->createToken("Login"))->plainTextToken];
    }

    //Login
    public function login(Request $request)
    {
        //Validate incoming data
        [
            "whatsapp" => $whatsapp,
            "password" => $password,
        ] = $request->validate([
            "whatsapp" => "required|numeric",
            "password" => "required"
        ]);

        //Get user model
        $user = ModelsUser::where("whatsapp", $whatsapp)->firstOrFail();

        //Check the password
        if (!password_verify($password, $user->password)) {
            //Return the error response
            return response()->json([
                "error" => "Unauthorized"
            ], 401);
        }

        //Return the response with created token
        return $user->toArray() + ["token" => ($user->createToken("Login"))->plainTextToken];
    }
}
