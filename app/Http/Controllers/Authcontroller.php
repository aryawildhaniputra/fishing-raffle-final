<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Authcontroller extends Controller
{
    public function index()
    {
        return view('auth.signin-admin');
    }


    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', join(', ', $validator->messages()->all()))
                ->withInput()
                ->withErrors($validator->messages());
        }

        $credentials = $validator->safe()->only('username', 'password');


        if (!Auth::attempt($credentials)) {
            return back()->with('toast_error', 'Username atau password tidak valid!');
        }

        $request->session()->regenerate();

        return redirect()->route('admin.home');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
