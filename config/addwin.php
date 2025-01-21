<?php

use Illuminate\Support\Str;

$defaultUrl = env('RACHEL_URL', 'http://localhost:3000') ;
return [
    'broadconvo' => [
        'extension' => [
            'password' => env('BROADCONVO_EXTENSION_PASSWORD', 'default-password')
        ]
    ],
    'rachel' => [
        'url' => [
            'base' => $defaultUrl,
            'email' => env('RACHEL_EMAIL_QUERY', $defaultUrl.'/email/query'),
            'chat' => env('RACHEL_CHAT_QUERY', $defaultUrl.'/chat/query'),
            'voice' => [
                'query' => env('RACHEL_VOICE_QUERY', $defaultUrl.'/voice/query'),
                'filler' => env('RACHEL_VOICE_FILLER', $defaultUrl.'/voice/filler'),
                'info' => env('RACHEL_VOICE_INFO', $defaultUrl.'/voice/info'),
            ],
            'knowledgeBase' => [
                // POST: Uploads document like pdf, txt, csv, pptx, docx
                'upload' => env('RACHEL_KB_UPLOAD', $defaultUrl.'/kb/upload'),
                // POST: Put all the contents of the textarea to the knowledgebase
                'text' => env('RACHEL_KB_TEXT', $defaultUrl.'/kb/upload/text'),
                // GET: Load the list of existing knowledge base documents
                // POST: Add new document to the list
                'list' => env('RACHEL_KB_LIST', $defaultUrl.'/kb/list'),
            ]
        ],
    ],
    'crm' => []
];
