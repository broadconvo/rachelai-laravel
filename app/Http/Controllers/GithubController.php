<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GithubController extends Controller
{
    public function handle(): JsonResponse
    {
        $secret = env('GITHUB_WEBHOOK_SECRET');

        // Validate the payload signature
        $signature = 'sha256=' . hash_hmac('sha256', request()->getContent(), $secret);
        if (!hash_equals($signature, request()->header('X-Hub-Signature-256'))) {
            Log::warning('GitHub webhook signature mismatch');
            abort(403, 'Invalid signature');
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
