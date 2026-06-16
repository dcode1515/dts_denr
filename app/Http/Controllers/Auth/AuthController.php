<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //

    public function login()
    {
        return view('auth.login');
    }
     public function logout(Request $request)
        {
             Auth::logout();

            return redirect('/');
        }


    public function post_login(Request $request)
{
    // 1. Validate the incoming data
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required|min:8',
    ], [
        'email.required'    => 'Please enter your email address.',
        'email.email'       => 'Please provide a valid email address.',
        'password.required' => 'Password is required.',
        'password.min'      => 'Password must be at least 8 characters.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors(),      // field‑level errors
        ], 422);
    }

    // 2. Attempt authentication
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->filled('remember'))) {
        $request->session()->regenerate();

        // 3. Role‑based redirection
        $user = Auth::user();                       // get the authenticated user
        $redirect = url('/dashboard');              // default redirect

        // Role-based routing
        if ($user->role === 'Super Admin') {
            $redirect = url('/dashboard-super-admin');
        } elseif ($user->role === 'Admin') {
            $redirect = url('/dashboard-admin');
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Login successful.',
            'redirect' => $redirect,
        ]);
    }

    // 4. Invalid credentials
    return response()->json([
        'success' => false,
        'message' => 'Invalid login credentials. Please check your credentials and try again.'
    ], 401);
}
    public function create_incoming()
        {
            return view('admin.create_incoming');
        }
}
