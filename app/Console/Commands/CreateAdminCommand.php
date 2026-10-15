<?php

namespace App\Console\Commands;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    private const PASSWORD_MIN = 12;

    protected $signature = 'gym:create-admin {--email=} {--name=} {--gym= : Existing gym slug}';

    protected $description = 'Create an owner account using a private password prompt';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Use interactive prompts or pipe the password twice through stdin.');

            return self::FAILURE;
        }
        $gym = Gym::where('slug', $this->option('gym'))->first();
        if (! $gym) {
            $this->error('Gym not found. Seed a tenant first.');

            return self::FAILURE;
        }
        $data = ['email' => $this->option('email'), 'name' => $this->option('name')];
        $validator = Validator::make($data, ['email' => 'required|email|unique:users,email', 'name' => 'required|string|max:255']);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        $password = $this->secret('Password (minimum 12 characters)');
        $confirmation = $this->secret('Confirm password');
        if (! is_string($password) || strlen($password) < self::PASSWORD_MIN || $password !== $confirmation) {
            $this->error('Passwords must match and contain at least 12 characters.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($gym, $data, $password) {
            $user = User::create([...$data, 'password' => Hash::make($password)]);
            $gym->users()->syncWithoutDetaching([$user->id => ['role' => 'owner', 'status' => 'active']]);
        });
        $this->info('Admin '.$data['email'].' created.');

        return self::SUCCESS;
    }
}
