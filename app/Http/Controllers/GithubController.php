<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GithubController extends Controller
{
    public function handle(): JsonResponse
    {
        $validator = Validator::make(request()->headers->all(), [
            'x-hub-signature-256' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check if the header is present
                    if (!request()->hasHeader($attribute)) {
                        Log::warning("The {$attribute} header is missing.");
                        $fail("The {$attribute} header is missing.");
                        return;
                    }

                    // Validate the payload signature
                    $secret = env('GITHUB_WEBHOOK_SECRET');
                    $payload = request()->getContent();
                    $computedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

                    if (!hash_equals($computedSignature, request()->header($attribute))) {
                        Log::warning("The {$attribute} has an invalid signature.");
                        $fail("The {$attribute} has an invalid signature.");
                    }
                },
            ],
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }


        // Handle the push event
        $payload = request()->all();
        if ($payload['ref'] === 'refs/heads/main') {
            try {
                $process = new Process(['git', 'pull', 'origin', 'main']);
                $process->setWorkingDirectory(base_path());
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                Log::info('Git pull successful');
                return response()->json(['message' => 'Git pull successful'], 200);
            } catch (\Exception $e) {
                Log::error('Git pull failed: ' . $e->getMessage());
                return response()->json(['error' => 'Git pull failed'], 500);
            }
        }

        return response()->json(['message' => 'Event not handled'], 200);
    }
}
