<?php

namespace App\Jobs;

use Log;
use App\Models\User;
use App\Mail\WelcomeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    protected User $user;
    protected string $token;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Sending welcome email to ' . $this->user->email);
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user, $this->token));
        Log::info('Welcome email sent successfully to ' . $this->user->email);
    }
}
