<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

  use PasswordValidationRules;

  public function login(Request $request)
  {
    try {

      // Input Validation
      $request->validate([
        'email' => 'email|required',
        'password' => 'required'
      ]);

      // Check login credential
      $credentials = request(['email', 'password']);
      if (!Auth::attempt($credentials)) {
        return ResponseFormatter::error([
          'message' => 'Unauthorized'
        ], 'Authentication Failed', 500);
      }

      // Jika hash tidak sesuai maka beri error
      $user = User::where('email', $request->email)->first();
      if (!Hash::check($request->password, $user->password, [])) {
        throw new Exception('Invalid Credentials');
      }

      // Jika berhasil maka loginkan
      $tokenResult = $user->createToken('authToken')->plainTextToken;
      return ResponseFormatter::success([
        'access_token' => $tokenResult,
        'token_type' => 'Bearer',
        'user' => $user
      ], 'Authenticated');
    } catch (Exception $error) {

      return ResponseFormatter::error([
        'message' => 'Something went wrong',
        'error' => $error
      ], 'Authentication Failed', 500);
    }
  }

  public function register(Request $request)
  {
    try {
      
      $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => $this->passwordRules()
      ]);

      User::create([
        'name' => $request->name,
        'email' => $request->email,
        'address' => $request->address,
        'house_number' => $request->houseNumber,
        'phone_number' => $request->phoneNumber,
        'city' => $request->city,
        'password' => Hash::make($request->password)
      ]);

      $user = User::where('email', $request->email)->first();

      $tokenResult = $user->createToken('authToken')->plainTextToken;

      return ResponseFormatter::success([
        'access_token' => $tokenResult,
        'token_type' => 'Bearer',
        'user' => $user
      ]);

    } catch (Exception $error) {
      
      return ResponseFormatter::error([
        'message' => 'Something went wrong',
        'error' => $error
      ], 'Authentication Failed', 500);
    }
  }

  public function logout(Request $request)
  {
    $token = $request->user()->currentAccessToken()->delete();

    return ResponseFormatter::success($token, 'Token Revoked');
  }
}