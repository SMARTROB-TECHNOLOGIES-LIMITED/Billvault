<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if ($request->isMethod('post')) {
            $validatedData = Validator::make($request->all(['email','password']),[
                'email' => 'required|email|max:255',
                'password' => 'required',
            ],[
                'email.required' => 'Email Address is required',
                'email.email' => 'Valid Email is required',
                'password.required' => 'Password is required',
            ]);
            if ($validatedData->fails()) {
                return redirect()->back()->withErrors($validatedData->errors());
            }
            
            if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->back()->with('error', 'Invalid Email or Password');
            }
        }
        
        $pg = "Authentication";
        return view('admin.auth.login', compact(['pg']));
    }
    
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
    
    public function showChangePasswordForm() {
        return view('admin.change-password');
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            Session::flash('alert',['t'=>'Error','m'=>'Current password is incorrect.']); return redirect()->back();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        Session::flash('alert',['t'=>'Success','m'=>'Password changed successfully.']); 
        
        return redirect()->route('admin.dashboard');
    }
}
