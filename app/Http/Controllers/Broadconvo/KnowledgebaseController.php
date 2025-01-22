<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\Knowledgebase;
use Illuminate\Http\Request;

class KnowledgebaseController extends Controller
{
    public function show($knowledgebaseId)
    {
        $knowledgebase = Knowledgebase::with(['entries' => function ($query) {
                $query->latest();
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


}
