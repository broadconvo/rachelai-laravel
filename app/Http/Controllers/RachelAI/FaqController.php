<?php

namespace App\Http\Controllers\RachelAI;

use App\Agents\FaqAgent;
use App\Http\Controllers\Controller;
use App\Models\Broadconvo\UserMaster;
use App\Models\User;
use App\Services\GmailService;
use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TomShaw\GoogleApi\GoogleClient;
use TomShaw\GoogleApi\Models\GoogleToken;

class FaqController extends Controller
{
    public function generate()
    {
        request()->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', request('email'))->first();
        auth()->loginUsingId($user->id);

        $userMaster = UserMaster::with('userAgent')
            ->whereEmail($user->email)
            ->first();

        if (!$userMaster) {
            return response(['message' => 'User not found in CRM']);
        }


        $agentId = $userMaster->userAgent->agent_id;



        /*
        |--------------------------------------------------------------------------
        | Step 1: Refresh and Retrieve email-sent items
        |--------------------------------------------------------------------------
        */
        $gmailService = new GmailService();

        // Refresh token
        $gmailService->refreshToken();
        // returns all collected sent-items that was saved in DB
        $sentItems = $gmailService->getSentItems();

        dd($sentItems);
        /*
        |--------------------------------------------------------------------------
        | Step 2: Generate FAQs based from the email-sent items
        |--------------------------------------------------------------------------
        |
        |   Reorganize the items in the following format:
        |   Email #1
        |   <sent item>
        |
        |
        */
        $reformattedSentItems = collect($sentItems)
            ->map(function ($email, $index) {
                return "Email #".($index + 1).":\n".$email->content;
            })->implode("\n");

        $faqAgent = new FaqAgent();
        $faqResult = $faqAgent->handle([
            'input' => 'Generate an FAQ from the following emails',
            'sentItems' => $reformattedSentItems,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Step 3: Create the knowledge base document
        |--------------------------------------------------------------------------
        |
        |   create the knowledgebase if none exists
        |   else use the existing knowledgebase
        |
        */

        // 3.1: load all existing knowledge base documents
        // /kb/list?rachel_id=34682642

        // UserAgent > Tenant > rachel_tenant
        // New Rachel will be added under rachel_tenant
        // the id that will be generated under rachel_tenant is the $rachelId
        $userMaster = UserMaster::with('userAgent.tenant.knowledgebases')
            ->whereEmail($user->email)->first();
        $rachel = $userMaster->userAgent->tenant->rachels[0];
        $rachelId = $rachel->rachel_id;
        $knowledgebases = $rachel->knowledgebases;

        $existingDocuments = collect($knowledgebases);

        // check if Email FAQ already exists
        $selectedItem = $existingDocuments->filter(function ($item) {
            return stripos($item['kb_label'], 'Email FAQs') !== false;
        });

        // creates new file if it does not exist yet
        if(!count($selectedItem)){
            // 3.2: create filename for the next knowledge base document and push to the list
            $filename = 'master.' . str()->uuid() . '.txt';

            $existingDocuments = $existingDocuments->push([
                'file' => $filename,
                'name' => 'Email FAQs',
                'industry' => ''
            ]);

            $addToListPayload = [
                'kb_label' => 'Email FAQs',
                'agent_id' => $agentId,
                'rachel_id' => $rachelId,
                'data' => $existingDocuments->toArray(),
                'index' => count($existingDocuments)-1
            ];

            $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
            $listUrl = config('addwin.rachel.url.knowledgeBase.list');
            $addToListResponse = Http::withHeaders($headers)->post($listUrl, $addToListPayload);

            if ($addToListResponse->failed()) {
                abort($addToListResponse->status(), 'Error occurred: '.$addToListResponse->body());
            }

            Log::info('Successfully created new knowledge base document ' . $filename);
        }
        else {
            $selectedItemIndex = $existingDocuments->search(function ($item) {
                return stripos($item['kb_label'], 'Email FAQs') !== false;
            });
            $filename = $selectedItem[$selectedItemIndex]['kb_id'];
        }


        /*
        |--------------------------------------------------------------------------
        | Step 4: Put FAQ into the document
        |--------------------------------------------------------------------------
        |
        |   Requirements:
        |   $rachelId, $filename, $faqResult
        |   Rachel's API /kb/upload/text will be used to manually add the text
        |   into the $filename
        |
        */

        $uploadUrl = config('addwin.rachel.url.knowledgeBase.text');
        $uploadPayload = [
            'agent_id' => $agentId,
            'rachel_id' => $rachelId,
            'kb_file' => $filename,
            'training_data' => $faqResult->value['content']
        ];
        $uploadResponse = Http::withHeaders($headers)->post($uploadUrl, $uploadPayload);

        if ($uploadResponse->failed()) {
            abort($uploadResponse->status(), 'Error occurred: '.$uploadResponse->body());
        }

        Log::info('Successfully inserted FAQ into the document ' . $filename);

        return response()->json(['message' => 'Successfully added knowledge base document.']);
    }
}
