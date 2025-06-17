<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending with MailHog';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';
        
        try {
            Mail::raw('Test email from DataTable Pro', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - DataTable Pro')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info("Test email sent to: {$email}");
            $this->info("Check MailHog at: http://localhost:8025");
            
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
