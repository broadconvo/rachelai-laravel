<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\Knowledgebase;
use App\Models\Broadconvo\UserAgent;
use App\Models\Broadconvo\UserMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KnowledgebaseController extends Controller
{
    public function create()
    {
        request()->validate([
            'creator_email' => ['required', 'email', 'exists:broadconvo.user_master,email'],
            'rachel_id' => ['required', 'string', 'exists:broadconvo.rachel_tenant,rachel_id'],
            'label' => ['required', 'string', 'max:255'],
            'industry' => ['string', 'max:255'],
            'entry.content' => ['required', 'string'],
            'entry.language' => ['required', 'string'],
            'entry.new_text' => ['string'],
        ]);

        $knowledgebase = DB::connection('broadconvo')->transaction(function(){

            $knowledgebase = Knowledgebase::create([
                'rachel_id' => request('rachel_id'),
                'kb_id' => 'master.'.str()->uuid().'.txt',
                'kb_label' => request('label'),
                'kb_industry' => request('industry'),
            ]);

            $master = UserMaster::with('userAgent')
                ->whereEmail(request('creator_email'))
                ->first();

            $agentId = $master->userAgent->agent_id;

            $knowledgebase->addEntry([
                'kb_content' => request('entry.content'),
                'kb_new_text' => request('entry.new_text') ?? null,
                'kb_language' => request('entry.language'),
                'kb_metadata' => request('entry.metadata') ?? null,
                'created_by' => $agentId,
                'updated_by' => $agentId,
            ]);

            return $knowledgebase;
        }); // end DB transaction

        if(!$knowledgebase)
            abort(422, 'Failed to create knowledgebase');

        return response()->json([
            'message' => 'Successfully added new knowledgebase',
            'data' => $knowledgebase
        ]);
    }
    public function show($knowledgebaseId)
    {
        $knowledgebase = Knowledgebase::with(['entries' => function ($query) {
                $query->oldest();
            }])
            ->whereKbId($knowledgebaseId)->first();

        return response()->json([
           'data' => $knowledgebase
        ]);
    }

    public function download($knowledgebaseId)
    {
        $knowledgebase = Knowledgebase::with(['entries' => function ($query) {
                $query->latest();
            }])
            ->whereKbId($knowledgebaseId)->first();

        // Check if knowledgebase exists
        if (!$knowledgebase) {
            return response()->json(['error' => 'Knowledgebase not found'], 404);
        }

        // Get the latest entry
        $entry = $knowledgebase->entries->first();

        if (!$entry) {
            return response()->json(['error' => 'No entries found'], 404);
        }

        // Content to be written into the file
        $content = $entry->kb_content ?? 'No content available';

        // Return the file as a download response
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $knowledgebase->kb_label . '.txt');
    }

    public function destroy($knowledgebaseId)
    {
        $knowledgebase = Knowledgebase::whereKbId($knowledgebaseId)->first();

        if(!$knowledgebase)
            abort(404, 'Knowledgebase not found');

        if(!$knowledgebase->delete()) {
            abort(422, 'Failed to delete knowledgebase');
        }

        return response()->json([
           'message' => 'Successfully deleted the knowledgebase'
        ]);
    }
}
