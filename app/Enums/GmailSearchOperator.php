<?php

namespace App\Enums;

enum GmailSearchOperator: string
{
    // Basic Search Operators
    case FROM = 'from';               // Search for emails from a specific sender
    case TO = 'to';                   // Search for emails sent to a specific recipient
    case CC = 'cc';                   // Search for emails where a specific address is in CC
    case BCC = 'bcc';                 // Search for emails where a specific address is in BCC
    case SUBJECT = 'subject';         // Search for emails with a specific subject
    case LABEL = 'label';             // Search for emails with a specific label
    case HAS = 'has';                 // Search for emails with specific attributes
    case IS = 'is';                   // Search for emails with a specific status
    case FILENAME = 'filename';       // Search for emails with a specific filename attachment

    // Date Operators
    case BEFORE = 'before';           // Search for emails received before a specific date
    case AFTER = 'after';             // Search for emails received after a specific date
    case OLDER_THAN = 'older_than';   // Search for emails older than a specific time period
    case NEWER_THAN = 'newer_than';   // Search for emails newer than a specific time period

    // Content Operators
    case EXACT_PHRASE = '"';          // Search for an exact phrase
    case OR = 'OR';                   // Search for emails matching one of multiple terms
    case NOT = '-';                   // Exclude emails containing a specific term
    case IN = 'in';                   // Search for emails in a specific folder or section

    // Special Operators
    case SIZE = 'size';               // Search for emails larger than a specific size (bytes)
    case LARGER = 'larger';           // Search for emails larger than a specific size
    case SMALLER = 'smaller';         // Search for emails smaller than a specific size
    case LIST = 'list';               // Search for emails from a specific mailing list
    case CATEGORY = 'category';       // Search for emails in a specific Gmail category

    // Has Attributes
    case HAS_ATTACHMENT = 'has:attachment'; // Emails with attachments
    case HAS_DRIVE = 'has:drive';           // Emails with Google Drive attachments
    case HAS_DOCUMENT = 'has:document';     // Emails with Google Docs attachments
    case HAS_SPREADSHEET = 'has:spreadsheet'; // Emails with Google Sheets attachments
    case HAS_PRESENTATION = 'has:presentation'; // Emails with Google Slides attachments

    // Utility Functions
    public static function listOperators(): array
    {
        return array_column(self::cases(), 'value');
    }
}
