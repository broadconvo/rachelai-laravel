<?php

namespace App\Console\Commands;

use App\Http\Controllers\RachelAI\FaqController;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateFaqCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:generate-faq';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate FAQs from email sent items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = '--- Starting to generate FAQs ---';
        Log::info($message);
        $this->info($message);
        // list all email that has Google tokens

        $emails = User::withValidGoogleTokens()->get('email')->pluck('email');

        foreach($emails as $email) {
            // Simulate a request for the FaqController
            request()->merge([
                'email' => $email,
                'is_command' => true
            ]);

            // Call the generate function
            $faqController = new FaqController();
            $faqController->generate($this);
        }

    }
}
