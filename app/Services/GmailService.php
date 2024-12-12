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
        $messagesResponse = $this->service->users_messages->listUsersMessages('me', ['q' => 'is:unread', 'maxResults' => 10]);

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
}