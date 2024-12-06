<?php

namespace App\Http\Controllers;

use App\Agents\EmailResponderAgent;
use App\Agents\TranslatorAgent;
use Illuminate\Http\Request;

class Email extends Controller
{
    /**
     * Create draft
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $emailAgent = new EmailResponderAgent();

        $result = $emailAgent->handle([
            'input' => 'Create a draft using the same language found in the provided email.',
            'body' => $request->body
        ]);

        $translateAgent = new TranslatorAgent();

        $englishResult = $translateAgent->handle([
            'input' => 'Translate in English',
            'body' => $result->content()
        ]);

        return response()->json([
            'response' => $result->content(),
            'english' => $englishResult->content()
        ]);
    }
}
