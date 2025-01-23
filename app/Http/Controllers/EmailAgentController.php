<?php

namespace App\Http\Controllers;

use App\Agents\EmailAgent;
use App\Agents\TranslatorAgent;
use App\Enums\GmailOperation;
use App\Enums\GmailSearchOperator;
use App\Models\EmailFilter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailAgentController extends Controller
{
    public function googleStatus()
    {
        request()->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', request('email'))->first();

        $message = ['message' => 'Google account not yet connected', 'status' => false];
        if($user->googleToken?->access_token) {
            $message = ['message' => 'Your account is connected', 'status' => true];
        }

        return response()->json($message);
    }
    /**
     * Create draft
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $emailAgent = new EmailAgent();

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

    public function createFilters()
    {
        request()->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'value' => ['required']
        ]);

        $user = User::where('email', request('email'))->first();
        request()->validate([
            'operator' => [
                'required',
                Rule::in(GmailSearchOperator::listOperators()), // Validate operator against enum values
            ],
            'operation' => [
                'required',
                Rule::in(GmailOperation::listOperations()), // Validate operator against enum values
            ],

        ],
            [
            ]);

        EmailFilter::updateOrcreate(
            [
                'user_id' => $user->id,
                'operation' => request('operation')
            ],
            [
                'user_id' => $user->id,
                'operator' => request('operator'),
                'value' => request('value'),
                'operation' => request('operation'),
            ]
        );

        return response()->json([
            'message' => 'Successfully created or updated the filter'
        ]);
    }

    public function getFilters()
    {
        $user = User::where('email', request('email'))->first();
        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ]);
        }

        if(!count($user->emailFilters)) {
            return response(['message' => 'No filters found']);
        }

        return response()->json([
            'data' => $user->emailFilters()->latest()->get()
        ]);
    }
}
