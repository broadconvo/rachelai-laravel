<?php

namespace App\Console\Commands;

use App\Services\GmailService;
use Google\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TomShaw\GoogleApi\GoogleClient;
use TomShaw\GoogleApi\Models\GoogleToken;

class GenerateDraftCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:generate-drafts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate drafts for Gmail messages';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = '--- Starting DRAFT - read_inbox ---';
        Log::info($message);
        $this->info($message);

        // Retrieve all active Google tokens
        $tokens = GoogleToken::all();

        if ($tokens->isEmpty()) {
            $this->info('No active tokens found.');
            return;
        }

        foreach ($tokens as $token) {
            $this->line('<fg=green>' . str_repeat('-', 50) . '</>');
            $user =  Auth::loginUsingId($token->user_id);

            $this->info("Processing messages for {$user->email}...");

            // Process the messages for the user
            $this->processMessages($user, $token);
        }

        $this->line('<fg=green>' . str_repeat('-', 50) . '</>');
        $this->info('--- End of process ---');
    }

    private function processMessages($user, $token)
    {
        $this->info('Retrieving messages ...');
        try {
            // Initialize Google Client and set the token manually

            // Initialize Gmail Service
            $gmailService = new GmailService();
            $gmailService->refreshToken();
            $messages = $gmailService->getUserMessages();

            if (!$messageCount = count($messages)) {
                $this->info("No new messages for User: {$user->email}");
                return;
            }

            $this->info("Unread messages found: $messageCount");
            foreach ($messages as $message) {
                if ($gmailService->hasDraft($message['id'])) {
                    $this->info("Draft already exists for message ID: {$message['id']}");
                    continue;
                }

                // check if email is related to business
                $postData = [
                    'message' => $message['body'],
                    'language' => 'English',
                    'uniqueId' => '1734315099.149537',
                    'rachelId' => '34682642', // for Addwin Customer Service
                    'sender' => $user->name
                ];
                $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];

                // Send the request to Rachel
                // going to /email/query
                $response = Http::withHeaders($headers)->post(config('addwin.rachel.url.email'), $postData);

                $responseBody = 'No response from Rachel.';
                if ($response->successful()) {
                    // Get the response body as an array
                    $responseBody = $response->json()['response'];
                } else {
                    // Handle errors
                    abort($response->status(), 'Error occurred: '.$response->body());
                }

                // Create a draft reply
                $gmailService->createDraft($message['sender'], $message['id'], $responseBody);

                $this->info("* Created draft in message ID: {$message['id']} for User: {$user->email}");
                Log::info("* Created draft in message ID: {$message['id']} for User: {$user->email}");
            }

        } catch (\Exception $e) {
            $this->error("Process failed for User: {$user->email}");
            Log::error("Error processing User: {$user->email}: " . $e->getMessage());
            // Log::error($e->getTraceAsString());
        }
    }
}
