<?php

namespace App\Services;

use Google\Service\Gmail;
use TomShaw\GoogleApi\GoogleClient;

class GmailService
{
    protected Gmail $service;

    public function __construct(GoogleClient $client)
    {
        $this->service = new Gmail($client());
    }

    public function getUserMessages()
    {
        $from = env('GMAIL_FROM_FILTER') ? 'from: '.env('GMAIL_FROM_FILTER') : '';
        $messagesResponse = $this->service
            ->users_messages
            ->listUsersMessages('me',
                [
                    'q' => 'is:unread ' . $from,
                    'maxResults' => 10
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

    public function createDraft($to, $threadId, $body, $from = null)
    {
        // If $from is not specified, use "me" (the authenticated user)
        $from = $from ?? 'me';

        // Construct a raw MIME message
        $rawMessage =
            "From: $from\r\n" .
            "To: $to\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 7bit\r\n\r\n" .
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
