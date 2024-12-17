<?php

namespace App\Http\Controllers;

use App\Agents\EmailAgent;
use App\Models\User;
use App\Services\GmailService;
use Laravel\Socialite\Facades\Socialite;
use TomShaw\GoogleApi\GoogleClient;
use TomShaw\GoogleApi\Models\GoogleToken;

class GmailController extends Controller
{
    public function googleRedirect()
    {
        $phoneNumbers = request()->query('phoneNumbers');

        $scopes = array_merge_recursive(
            config('google-api.service_scopes'),
            [
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/userinfo.email',
                'openid'
            ]
        );
        $state = base64_encode($phoneNumbers);

        $targetUrl = Socialite::with('google')
            ->with(['access_type' => 'offline', 'prompt' => 'consent select_account', 'state'=>$state])
            ->scopes($scopes)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
        return redirect($targetUrl);
    }

    public function index(GoogleClient $client)
    {
        $phoneNumbers = base64_decode(request('state'));

        // Regular expression to extract all numbers and flatten
        preg_match_all('/\d+/', $phoneNumbers, $phoneMatches);

        // Join numbers into full phone numbers
        $phoneNumbers = array_chunk($phoneMatches[0], 2);
        $cleanedNumbers = implode(';', array_map(fn($pair) => implode('', $pair), $phoneNumbers));

        $oauthUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $oauthUser->email], // Search criteria
            [
                'name' => $oauthUser->name,
                'phone_number' => $cleanedNumbers,
                'password' => bcrypt('password'),
            ]
        );

        $googleToken = GoogleToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'access_token' => $oauthUser->token,
                'refresh_token' => $oauthUser->refreshToken,
                'expires_in' => 30 * 24 * 60 * 60, // 1 month in seconds,
                'scope' => request('scope'),
                'token_type' => 'Bearer',
                'created' => now()->timestamp,
            ]
        );

        return response()->json([
            'token' => $googleToken->access_token,
            'refreshToken' => $googleToken->refresh_token,
        ]);
    }

    // should be authenticated
    public function getEmails()
    {
        // Google Client automatically retrieves the google-token based from the logged-in user's id
        // this is done via Tomshaw's GoogleClient
        // We created a custom Gmail Service to handle the Gmail API
        $gmailService = new GmailService(app(GoogleClient::class));
        $messages = $gmailService->getUserMessages();

        if(!count($messages)) {
            return response()->json(['message' => 'No new messages']);
        }

        $emailAgent = new EmailAgent();

        $aiResponses = [];
        foreach ( $messages as $message ) {
            $result = $emailAgent->handle([
                'input' => 'Create a draft message using the same language as the provided email.',
                'body' => $message['body'],
                'sender' => $message['sender']
            ]);

            $gmailService->createDraft($message['sender'], $message['id'], $result->content());

            $aiResponses[] = [
                'id' => $message['id'],
                'message' => $message['body'],
                'response' => $result->content(),
                'to' => $message['sender']
            ];
        } // end foreach


        return response()->json(['response' =>  $aiResponses]);
    }

    public function watchGmail()
    {
        $gmailService = new GmailService(app(GoogleClient::class));
        $response = $gmailService->watchGmail();

        return response()->json($response);
    }
}
