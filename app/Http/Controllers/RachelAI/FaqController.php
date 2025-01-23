<?php

namespace App\Http\Controllers\RachelAI;

use App\Agents\FaqAgent;
use App\Http\Controllers\Controller;
use App\Models\Broadconvo\Knowledgebase;
use App\Models\Broadconvo\UserMaster;
use App\Models\User;
use App\Services\GmailService;
use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TomShaw\GoogleApi\GoogleClient;
use TomShaw\GoogleApi\Models\GoogleToken;
use function PHPUnit\Framework\isEmpty;

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

        if(!$sentItems) {
            Log::info('No additional sent items to generate for FAQ');
            abort(422, 'No additional sent items to generate for FAQ');
        }

        Log::info('New additional sent item to generate for FAQ');
        $reformattedSentItems = collect($sentItems)
            ->map(function ($email, $index) {
                return "Email #".($index + 1).":\n".$email['content'];
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
        $knowledgebaseLabel = 'Email FAQs';

        $selectedItem = $existingDocuments->filter(function ($item) use($knowledgebaseLabel) {
            return stripos($item['kb_label'], $knowledgebaseLabel) !== false;
        })->first();

        // new filename if there is no existing knowledgebase for Email FAQ
        $knowledgebaseId = optional($selectedItem)->kb_id ?? 'master.'.str()->uuid().'.txt';
        $knowledgebase = Knowledgebase::updateOrCreate(
            ['kb_id' => $knowledgebaseId ] ,
            [
                'rachel_id' => $rachelId,
                'kb_label' => $knowledgebaseLabel,
            ]
        );

        if(!$knowledgebase){
            Log::error('Failed to create or update knowledgebase: ' . $knowledgebaseId);
            abort(422, 'Failed to create knowledgebase');
        }
        Log::info($selectedItem
            ? 'Existing knowledgebase retrieved: '. $knowledgebaseId
            : 'New knowledgebase created: '. $knowledgebaseId);

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

        $master = UserMaster::with('userAgent')
            ->whereEmail(request('email'))
            ->first();

        $agentId = $master->userAgent->agent_id;

        // concatenate existing content to new faq-content
        $content = $faqResult->value['content'];

        if($selectedItem) {
            $entries = $knowledgebase
                ->entries()
                ->latest()
                ->first();

            if($entries)
                $content = $entries->kb_content . "\n\n". $content;
            else
                Log::info('No previous knowledgebase entries found');
        }

        // Always create new entry for EMAIL FAQ knowledgebase
        $knowledgebase->addEntry([
            'kb_content' => $content,
            'kb_language' => 'en',
            'created_by' => $agentId,
            'updated_by' => $agentId,
        ]);

        Log::info('Successfully created entry in knowledgebase: ' . $knowledgebaseId);

        /*
        |--------------------------------------------------------------------------
        | Step 5: Use Rachel AI Legacy functions
        |--------------------------------------------------------------------------
        |
        |   use POST /kb/list to reload the CSV file:
        |   use POST /kb/upload/text to process the upload
        |
        */
        $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];


        // reload CSV file
        $knowledgebases[] = $knowledgebase;
        $csvListData = $knowledgebases->map(function($document) {
            return [
                'file' => $document->kb_id,
                'name' => $document->kb_label,
                'industry' => $document->kb_industry,
            ];
        });

        if(!count($csvListData)){
            Log::error('No knowledgebase list to send. '. json_encode($csvListData) );
            abort(422, 'No knowledgebase list to send.');
        }

        $csvListUrl = config('addwin.rachel.url.knowledgeBase.list');
        $csvListPayload = [
            'data' => $csvListData->toArray(),
            'rachel_id' => $rachelId,
        ];
        Log::info(json_encode($csvListPayload));

        $csvListResponse = Http::withHeaders($headers)->post($csvListUrl, $csvListPayload);
        if ($csvListResponse->failed()) {
            abort($csvListResponse->status(), 'Failed to do reloadCsv(): '.$csvListResponse->body());
        }

        // imitate processUpload by calling /kb/upload/text
        // this is going to create a vector based on the listed files in CSV
        $processUploadUrl = config('addwin.rachel.url.knowledgeBase.text');
        $processUploadPayload = [
            'agent_id' => $agentId,
            'rachel_id' => $rachelId,
            'kb_file' => $knowledgebaseId,
            'training_data' => $content,
            'kb_label' => $knowledgebaseLabel
        ];
        $processUploadResponse = Http::withHeaders($headers)->post($processUploadUrl, $processUploadPayload);

        if ($processUploadResponse->failed()) {
            abort($processUploadResponse->status(), 'Failed to do processUpload(): '.$processUploadResponse->body());
        }


        return response()->json([
            'message' => 'Successfully processed knowledgebase.',
            'data' => [
                'file' => $knowledgebaseId,
                'label' => $knowledgebaseLabel,
                'rachel_id' => $rachelId
            ]
        ]);
    }
}
