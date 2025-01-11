<?php

namespace App\Http\Controllers\RachelAI;

use App\Agents\FaqAgent;
use App\Http\Controllers\Controller;
use App\Models\Broadconvo\UserMaster;
use App\Services\GmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TomShaw\GoogleApi\GoogleClient;

class FaqController extends Controller
{
    public function generate()
    {
        $user = auth()->loginUsingId(3);

        if(!$user->googleToken?->access_token) {
            return response(['message' => 'Action not allowed by user']);
        }

        $userMaster = UserMaster::with('userAgent')
            ->whereEmail($user->email)
            ->first();

        if (!$userMaster) {
            return response(['message' => 'User not found in CRM']);
        }

        $agentId = $userMaster->userAgent->agent_id;



        /*
        |--------------------------------------------------------------------------
        | Step 1: Retrieve email-sent items
        |--------------------------------------------------------------------------
        */
        $gmailService = new GmailService(app(GoogleClient::class));
        $sentItems = $gmailService->getSentItems();


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
                return "Email #".($index + 1).":\n".$email['body'];
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
        $rachelId = '09238402';
        $listUrl = config('addwin.rachel.url.knowledgeBase.list');
        $listResponse = Http::get($listUrl.'?rachel_id='.$rachelId);

        if ($listResponse->failed()) {
            abort($listResponse->status(), 'Error occurred: '.$listResponse->body());
        }

        $existingDocuments = collect($listResponse->json());

        // 3.2: create filename for the next knowledge base document and push to the list
        $filename = 'master.' . str()->uuid() . '.txt';

        $existingDocuments = $existingDocuments->push([
            'file' => $filename,
            'name' => 'Knowledgebase '.count($existingDocuments)+1,
            'industry' => ''
        ]);

        $addToListPayload = [
            'agent_id' => $agentId,
            'rachel_id' => $rachelId,
            'data' => $existingDocuments->toArray(),
            'index' => count($existingDocuments)-1
        ];

        $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];

        $addToListResponse = Http::withHeaders($headers)->post($listUrl, $addToListPayload);


        if ($addToListResponse->failed()) {
            abort($addToListResponse->status(), 'Error occurred: '.$addToListResponse->body());
        }

        Log::info('Successfully created knowledge base document ' . $filename);

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
