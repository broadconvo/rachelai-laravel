<?php

namespace App\Services;

use App\Enums\GmailOperation;
use App\Models\GmailSentItem;
use App\Models\User;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\WatchRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TomShaw\GoogleApi\GoogleClient;
use TomShaw\GoogleApi\Models\GoogleToken;
use function Laravel\Prompts\error;

class GmailService
{
    protected Gmail $service;
    protected Client $client;

    public function __construct()
    {
        $this->client = app(Client::class);
        $this->client->setAccessToken(auth()->user()->googleToken->access_token);
        $this->service = new Gmail($this->client);
    }

    public function getUserMessages()
    {
        // NOTE: Only retrieves email filters that has operation: read_inbox

        $filters = auth()->user()
            ->emailFilters()
            ->whereOperation(GmailOperation::READ_INBOX)
            ->get();

        $filters = $filters
            ->map(fn($filter) => $filter->operator.':'.$filter->value)
            ->implode(' ');

        // TODO: filter should be the user's desired to: filter
        // to:myuan@broadconvo.com
        // User wants to only check for emails that goes to said filter
        $messagesResponse = $this->service
            ->users_messages
            ->listUsersMessages('me',
                [
                    'q' => 'is:unread in:inbox '.$filters,
                    'maxResults' => 10
                ]);
        $messages = [];

        foreach ($messagesResponse->getMessages() as $message) {
            $messages[] = $this->getMessageDetails($message->getId());
        }

        return $messages;
    }

    public function getSentItems()
    {
        // NOTE: Only retrieves email filters that has operation: read_sent

        // TODO: i need subject, from, to, date, message-id, body
        // get the email from the sender attribute:
        //  Ground Breaker <groundbreaker08@gmail.com>

        // e.g: "from:some@example.com"
        $filters = auth()->user()
            ->emailFilters()
            ->whereOperation(GmailOperation::READ_SENT)
            ->get();

        $filters = $filters
            ->map(fn($filter) => $filter->operator.':'.$filter->value)
            ->implode(' ');

        $messagesResponse = $this->service
            ->users_messages
            ->listUsersMessages('me',
                [
                    'q' => 'is:sent '.$filters,
                ]);
        $messages = [];

        foreach ($messagesResponse->getMessages() as $message) {
            $messages[] = $this->getMessageDetails($message->getId());
        }

        return $messages;
    }
    /**
     * Get the details of a single message.
     */
    public function getMessageDetails(string $messageId): array
    {
        $message = $this->service->users_messages->get('me', $messageId);
        $payload = $message->getPayload();
        $headers = $message->getPayload()->getHeaders();

        $body = '';
        $sender = '';

        // Extract the 'From' header for the sender's email
        foreach ($headers as $header) {
            if (strtolower($header->getName()) === 'from') {
                $sender = $header->getValue();
                break;
            }
        }

        // Try to get the plain text or HTML body
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() === 'text/plain' || $part->getMimeType() === 'text/html') {
                    $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $part->getBody()->getData()));
                    break;
                }
            }
        } else {
            // If there's no parts, check the body directly
            $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload->getBody()->getData()));
        }

        return [
            'id' => $message->getId(),
            'snippet' => $message->getSnippet(),
            'body' => $body,
            'sender' => $sender,
        ];
    }

    public function refreshToken(GoogleToken $googleToken)
    {
        $client = new Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->refreshToken($googleToken->refresh_token); // Your stored refresh token

        $newToken = $client->fetchAccessTokenWithRefreshToken($googleToken->refresh_token);

        GoogleToken::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'access_token' => $newToken['access_token'],
                'refresh_token' => $newToken['refresh_token'],
                'expires_in' => 3600, // 1hr in seconds
            ]
        );
    }

    public function createDraft($to, $threadId, $body, $from = null)
    {
        // If $from is not specified, use "me" (the authenticated user)
        $from = $from ?? 'me';

        // Construct a raw MIME message
        $rawMessage =
            "From: $from\r\n".
            "To: $to\r\n".
            "MIME-Version: 1.0\r\n".
            "Content-Type: text/plain; charset=UTF-8\r\n".
            "Content-Transfer-Encoding: 7bit\r\n\r\n".
            $body;

        // Base64 URL-safe encode the message
        $encodedMessage = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');

        // Create a new Gmail Message
        $message = new Gmail\Message();
        $message->setRaw($encodedMessage);
        $message->setThreadId($threadId);

        // Create a new Draft
        $draft = new Gmail\Draft();
        $draft->setMessage($message);

        // Save the draft
        return $this->service->users_drafts->create('me', $draft);
    }

    public function hasDraft(string $messageId): bool
    {
        try {
            // Fetch the list of drafts
            $drafts = $this->service->users_drafts->listUsersDrafts('me')->getDrafts();

            foreach ($drafts as $draft) {
                // Retrieve the draft details
                $draftDetails = $this->service->users_drafts->get('me', $draft->getId());

                // Check if the draft's message matches the given message ID
                if ($draftDetails->getMessage()->getThreadId() === $messageId) {
                    return true; // Draft already exists
                }
            }

            return false; // No matching draft found
        } catch (\Exception $e) {
            // Log or handle the exception
            throw new \Exception("Error checking drafts: ".$e->getMessage());
        }
    }

    public function watchGmail()
    {
        // config('google-api.pubsub_topic')
        $project = 'broadconvo-email';
        $topic = "projects/{$project}/topics/gmail-watcher";
        $watchRequest = new WatchRequest([
            'topicName' => $topic,
            'labelIds' => ['INBOX'],
        ]);

        return $this->service->users->watch('me', $watchRequest);
    }
}
