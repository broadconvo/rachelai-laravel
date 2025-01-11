<?php

namespace App\Http\Controllers;

use App\Agents\EmailAgent;
use App\Agents\TranslatorAgent;
use App\Enums\GmailSearchOperator;
use App\Models\EmailFilter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailAgentController extends Controller
{
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
        ]);

        $user = User::where('email', request('email'))->first();
        request()->validate([
            'operator' => [
                'required',
                Rule::in(GmailSearchOperator::listOperators()), // Validate operator against enum values
            ],
            'value' => [
                'required', 'string',
                Rule::unique('email_filters') // Ensure the combination is unique
                    ->where(function ($query) use ($user){
                        $user = User::where('email', request('email'))->first();
                        return $query
                            ->where('user_id', $user ? $user->id : null)
                            ->where('operator', request('operator'));
                    }),
            ]
        ],
            [
                'value.unique' => 'The filter combination of user, operator, and value already exists.',
            ]);

        EmailFilter::updateOrCreate(
            ['user_id' => $user->id],
            [
                'operator' => request('operator'),
                'value' => request('value'),
            ]
        );

        return response()->json([
            'message' => 'Filter created successfully'
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

        $filter = EmailFilter::where('user_id', $user->id)->first();

        if(!$filter) {
            return response()->json([
                'message' => 'No filter found'
            ]);
        }

        return response()->json([
            'filter' => $filter->filters
        ]);
    }
}
