<?php

namespace App\Http\Controllers\Qdrant;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as HttpClient;
use Qdrant\Config as QdrantConfig;
use Qdrant\Http\Transport as QdrantTransport;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Qdrant;

class CollectionController extends Controller
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

    public function store()
    {
        $config = new QdrantConfig(config('qdrant.host'),config('qdrant.port'));
        $httpClient = new HttpClient();
        $config->setApiKey(config('qdrant.apiKey'));

        $client = new Qdrant(new QdrantTransport($httpClient, $config));

        $createCollection = new CreateCollection();

        $createCollection->addVector(
            new VectorParams(
                request('vectorSize') ?? config('qdrant.vectorSize'),
                request('distance') ?? VectorParams::DISTANCE_COSINE),
            request('name')
        );

        $client->collections('emails')->create($createCollection);

        return response()->json([
            'message' => 'Collection created',
        ]);
    }
}
