<?php

namespace App\Http\Controllers;

use App\Agents\EmailAgent;
use App\Agents\TranslatorAgent;
use App\Models\EmailFilter;
use App\Models\User;
use Illuminate\Http\Request;

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
        $user = User::where('email', request('email'))->first();
        $existingFilter = EmailFilter::where('user_id', $user->id)->first();

        EmailFilter::updateOrCreate(
            ['id' => $existingFilter->id],
            [
                'user_id' => $user->id,
                'filters' => request('filter')
            ]
        );

        return response()->json([
            'message' => 'Filter created successfully'
        ]);
    }

    public function getFilters()
    {
        $user = User::where('email', request('email'))->first();
        $filter = EmailFilter::where('user_id', $user->id)->first();

        return response()->json([
            'filter' => $filter->filters
        ]);
    }
}
