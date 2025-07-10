<?php
    namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Override resetPassword method to redirect back after successful reset
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @param string $password
     * @return \Illuminate\Http\RedirectResponse
     */
     
        public function redirectPath()
        {
            return route('password_update_response');
        }

    protected function resetPassword($user, $password)
    {
        // Update the user's password
        $user->password = Hash::make($password);
        $user->save();

        // Flash a success message
        session()->flash('status', 'Your password has been successfully reset.');

        // Redirect back to the password reset form with success message
       return redirect()->route('password_update_response');
    }
}

