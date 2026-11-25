<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('admin.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'max:255'],
            'password' => [
                'bail', 'required', 'string', 'min:14', 'confirmed', 'different:current_password',
                static function (string $attribute, string $value, Closure $fail): void {
                    if (strlen($value) > 72) {
                        $fail('The new password must be at most 72 bytes.');
                    } elseif (str_contains($value, "\0")) {
                        $fail('The new password contains an unsupported character.');
                    }
                },
            ],
        ], [
            'password.different' => 'Choose a new password that differs from your current password.',
        ]);

        $user = DB::transaction(function () use ($request, $data) {
            $user = User::lockForUpdate()->findOrFail($request->user()->getAuthIdentifier());
            abort_unless($user->is_admin, 403);

            if (! Hash::check($data['current_password'], $user->getAuthPassword())) {
                throw ValidationException::withMessages(['current_password' => 'Your current password is incorrect.']);
            }

            $user->password = $data['password'];
            $user->setRememberToken(Str::random(60));
            $user->save();

            return $user;
        });

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table'))
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        Auth::guard('web')->setUser($user);
        $request->session()->regenerate(true);

        return redirect()->route('admin.password.edit')->with('status', 'Password changed. Other sessions were signed out.');
    }
}
