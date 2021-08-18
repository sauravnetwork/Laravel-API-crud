<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illumiate\Http\Response;
use Illuminate\SUpport\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];
         
        return response($response, 201);
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

            //Check email
                $user=User::where('email', $fields['email'])->first();
            
            //Check password
                if(!$user || !Hash::check($fields['password'], $user->password)){
                    return response([ 
                    'message' => 'Bad creds'
                    ], 404);     
                }
                   
          $token = $user->createToken('myapptoken')->plainTextToken;

           $response = [ 
            'user' => $user,
            'token' => $token
        ];
         
        return response($response, 201);
    }

        public function logout(Request $request) {
            auth()->user()->tokens()->delete();
            return[
                'message' => 'Logged out'
            ];
        }

        public function forget_password(Request $request){
            $request->validate([
                'email' => 'required|email',]);
                
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)])
        ->middleware('guest')->name('password.email');
    }
}
