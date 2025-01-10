<?php

use Illuminate\Support\Str;

return [
    'host' => env('QDRANT_HOST', 'http://localhost'),
    'port' => env('QDRANT_PORT', 6333),
    'apiKey' => env('QDRANT_API_KEY', 'your-api-key'),
    // 768 for OpenAI embeddings
    'vectorSize' => env('QDRANT_VECTOR_SIZE', '1536'),
    'distance' => env('QDRANT_DISTANCE', 'Cosine'),
];
