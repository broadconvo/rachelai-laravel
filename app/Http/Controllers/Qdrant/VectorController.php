<?php

namespace App\Http\Controllers\Qdrant;

use App\Agents\EmailAgent;
use App\Http\Controllers\Controller;
use OpenAI\Laravel\Facades\OpenAI;
use Qdrant\Config as QdrantConfig;
use Qdrant\Http\Transport as QdrantTransport;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;
use Qdrant\Qdrant;
use GuzzleHttp\Client as HttpClient;

class VectorController extends Controller
{
    public function index()
    {
        $config = new QdrantConfig(config('qdrant.host'),config('qdrant.port'));
        $httpClient = new HttpClient();
        $config->setApiKey(config('qdrant.apiKey'));

        $client = new Qdrant(new QdrantTransport($httpClient, $config));

        $response = $client->collections()->list();

        $collectionNames = array_map(fn($collection) => $collection['name'],
            $response['result']['collections']);

        return response()->json([
            'collections' => $collectionNames,
        ]);
    }

    public function store($collection)
    {
        $config = new QdrantConfig(config('qdrant.host'),config('qdrant.port'));
        $httpClient = new HttpClient();
        $config->setApiKey(config('qdrant.apiKey'));

        $client = new Qdrant(new QdrantTransport($httpClient, $config));

        $emails = [
            [
                'id' => str()->uuid(),
                'value' => 'Hi all,

Please see the attached files.
Thanks Noel for the notes.

3. Red – Provide sample contracts and email templates in MS Word format (This week) > Attached sample quotation, invoice and contract in word. Also I just forwarded a sample greetings email for your reference. 
4.    Red – Provide calculation method for commissions (This week) > Attached the calculation excel and also some samples of our fee structure
5.    Red – Provide alternative email address for creating a new account in Broadconvo and CRM from Red (This week) > evergreenwork135@gmail.com

Let me know if you need more support.

Best regards,
Red
RED LAU | Mustard
+852 6200 6457
red@mustard.com.hk
www.mustard.com.hk'
            ]
        ];

        $result = OpenAI::embeddings()->create([
            'model' => config('synapse.integrations.openai.embedding_model'),
            'input' => collect($emails)->pluck('value')->toArray()
        ]);

        //dd(count($result->embeddings[0]->embedding));
        $points = new PointsStruct();

        // Loop through the sayings and prepare points
        foreach ($emails as $key => $email) {
            $points->addPoint(
                new PointStruct(
                    $email['id'], // Unique ID for the point
                    new VectorStruct($result->embeddings[$key]->embedding, $collection),
                    [
                        'body' => $email['value']
                    ]
                )
            );
        }

        $client->collections($collection)
            ->points()
            ->upsert($points, ['wait' => 'true']);

        return response()->json([
            'message' => 'Vector added to collection.',
        ]);
    }


    public function search($collection)
    {
        $config = new QdrantConfig(config('qdrant.host'),config('qdrant.port'));
        $httpClient = new HttpClient();
        $config->setApiKey(config('qdrant.apiKey'));

        $client = new Qdrant(new QdrantTransport($httpClient, $config));

        $result = OpenAI::embeddings()->create([
            'model' => config('synapse.integrations.openai.embedding_model'),
            'input' => request('query'),
        ]);

        $vectorStruct = new VectorStruct($result->embeddings[0]->embedding, $collection);
        $searchRequest = (new SearchRequest($vectorStruct))
            ->setLimit(5)
            ->setParams([
                'hnsw_ef' => 128,
                'exact' => true,
            ])
            ->setWithVector(false)
            ->setWithPayload(true);

        $response = $client->collections($collection)
            ->points()
            ->search($searchRequest);


        // Step 4: Extract Context from Search Results
        $context = collect($response['result'])->map(function ($result) {
            return preg_replace('/\s+/', ' ', trim($result['payload']['body'] ?? ''));
        })->filter()->implode("\n"); // Combine all into a single string with line breaks

        auth()->loginUsingId(3);
        // Step 5: Generate a Response Using OpenAI
        $emailAgent = new EmailAgent();
        $result = $emailAgent->handle([
            'input' => 'Create a draft message using the same language as the provided email.',
            'body' => request('query'),
            'context' => $context,
            'sender' => 'Noel',
            'owner' => auth()->user()->name,
        ]);


        return response($result);
    }

}
