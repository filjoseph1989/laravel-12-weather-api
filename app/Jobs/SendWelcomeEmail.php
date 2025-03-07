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

    /**
     * The user the email is being sent to.
     * @var User
     */
    protected User $user;

    /**
     * The token to be included in the email.
     * @var string
     */
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
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('Sending welcome email to ' . $this->user->email);
        try {
            Mail::to($this->user->email)->send(new WelcomeEmail($this->user, $this->token));
            Log::info('Welcome email sent successfully to ' . $this->user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email to ' . $this->user->email . ': ' . $e->getMessage());
        }
    }
}
