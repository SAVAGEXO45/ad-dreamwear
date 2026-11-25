<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdministrator extends Command
{
    protected $signature = 'catalogue:admin {email : Administrator email} {--name=Owner : Display name} {--reset : Reset the password of an existing administrator}';

    protected $description = 'Create an administrator or reset their password using a hidden prompt';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        $name = trim($this->option('name'));
        $existing = User::where('email', $email)->first();

        if (($existing && ! $this->option('reset')) || ($this->option('reset') && ! $existing?->is_admin)) {
            $this->error('This email cannot be used for the requested action.');

            return self::FAILURE;
        }

        $password = $this->secret('Password (at least 14 characters)', false);
        $confirmation = $this->secret('Repeat password', false);
        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'password_confirmation' => $confirmation],
            ['email' => 'required|email|max:255', 'name' => 'required|string|max:255', 'password' => 'required|string|min:14|confirmed']
        );
        if ($validator->fails() || strlen((string) $password) > 72) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            if (strlen((string) $password) > 72) {
                $this->error('Password must be at most 72 bytes.');
            }

            return self::FAILURE;
        }

        $user = $existing ?? new User(['name' => $name, 'email' => $email]);
        $user->forceFill(['password' => $password, 'is_admin' => true])->save();
        if ($existing && config('session.driver') === 'database') {
            \Illuminate\Support\Facades\DB::connection(config('session.connection'))
                ->table(config('session.table'))->where('user_id', $user->id)->delete();
        }

        $this->info('Administrator credentials saved.');

        return self::SUCCESS;
    }
}
