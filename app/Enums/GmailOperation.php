<?php

namespace App\Enums;

enum GmailOperation : string
{
    case READ_INBOX = 'read_inbox';
    case READ_SENT = 'read_sent';

    // Utility Functions
    public static function listOperations(): array
    {
        return array_column(self::cases(), 'value');
    }
}
