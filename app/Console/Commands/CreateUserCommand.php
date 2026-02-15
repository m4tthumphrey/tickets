<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    protected $signature = 'app:create-user';

    protected $description = 'Create a new admin user';

    public function handle(): int
    {
        $name = $this->ask('Name');

        if (! $name) {
            $this->error('Name is required.');
            return 1;
        }

        $email = $this->ask('Email');

        if (! $email) {
            $this->error('Email is required.');
            return 1;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists.');
            return 1;
        }

        $password = $this->secret('Password (leave blank to generate)');

        if (! $password) {
            $password = Str::random(10);
            $this->info("Generated password: {$password}");
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->info("User {$email} created successfully.");

        return 0;
    }
}
