<?php

namespace App\Console\Commands;

use App\Http\Controllers\RachelAI\FaqController;
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
        $this->info('--- Starting to generate FAQs ---');
        // list all email that has Google tokens

        $emails = [];

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
