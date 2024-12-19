<?php

namespace App\Console\Commands;

use App\Agents\EmailAgent;
use App\Models\User;
use App\Services\GmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use TomShaw\GoogleApi\GoogleClient;
use TomShaw\GoogleApi\Models\GoogleToken;

class ProcessGmailMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:process-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Gmail messages for all users with active tokens';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process Gmail messages...');

        // Retrieve all active Google tokens
        $tokens = GoogleToken::all();

        if ($tokens->isEmpty()) {
            $this->info('No active tokens found.');
            return;
        }

        foreach ($tokens as $token) {
            $user =  Auth::loginUsingId($token->user_id);

            $this->info("Processing messages for user {$user->email}...");

            // Process the messages for the user
            $this->processMessages($token);
        }

        $this->info('End of process.');
    }

    private function processMessages($token)
    {
        $this->info('Processing messages...');
        try {
            // Initialize Google Client and set the token manually

            // Initialize Gmail Service
            $gmailService = new GmailService(app(GoogleClient::class));
            $messages = $gmailService->getUserMessages();

            if (!count($messages)) {
                $this->info("No new messages for User ID: {$token->user_id}");
                return;
            }

            $emailAgent = new EmailAgent();
            foreach ($messages as $message) {
                if ($gmailService->hasDraft($message['id'])) {
                    $this->info("Draft already exists for message ID: {$message['id']}");
                    continue;
                }

                $result = $emailAgent->handle([
                    'input' => 'Create a draft message using the same language as the provided email.',
                    'body' => $message['body'],
                    'sender' => $message['sender']
                ]);

                // Create a draft reply
                $gmailService->createDraft($message['sender'], $message['id'], $result->content());

                $this->info("Processed message ID: {$message['id']} for User ID: {$token->user_id}");
            }

        } catch (\Exception $e) {
            $this->error("Error processing User ID {$token->user_id}: ".$e->getMessage());
        }
    }
}
