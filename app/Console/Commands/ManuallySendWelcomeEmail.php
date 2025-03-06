<?php

namespace App\Console\Commands;

use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Console\Command;

class ManuallySendWelcomeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:manually-send-welcome-email {user : ID or Email of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending welcome email to the user.');

        $userIdentifier = $this->argument('user');

        $testUser = User::where('id', $userIdentifier)
            ->orWhere('email', $userIdentifier)
            ->first();

        if (!$testUser && $userIdentifier === 'testuser@example.com') {
            $testUser = User::firstOrCreate(
                ['email' => 'testuser@example.com'],
                [
                    'name' => 'Test User',
                    'email' => 'testuser@example.com',
                    'password' => bcrypt('password')
                ]
            );
        }

        if (!$testUser) {
            $this->error('User not found.');
            return 1;
        }

        $testToken = $testUser->createToken('test-token')->plainTextToken;

        SendWelcomeEmail::dispatch($testUser, $testToken);

        $this->info('Welcome email job has been dispatched successfully. Make sure queue workers are running to process the job.');
    }
}
