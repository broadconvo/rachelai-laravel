<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GmailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use TomShaw\GoogleApi\GoogleClient;

class GmailController extends Controller
{
    public function googleRedirect()
    {
        $scopes = array_merge_recursive(
            config('google-api.service_scopes'),
            [
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/userinfo.email',
                'openid'
            ]
        );

        return Socialite::with('google')
            ->with(['access_type' => 'offline', 'prompt' => 'consent select_account'])
            ->scopes($scopes)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }

    public function index(GoogleClient $client)
    {
        $user = Socialite::driver('google')->stateless()->user();

        /**
         * array:9 [▼ // app/Http/Controllers/GmailController.php:43
         * "azp" => "644206466557-ibc8htql0hvmi1egism3cr93hp5o37fl.apps.googleusercontent.com"
         * "aud" => "644206466557-ibc8htql0hvmi1egism3cr93hp5o37fl.apps.googleusercontent.com"
         * "sub" => "103994001368389464510"
         * "scope" => "
         * https://www.googleapis.com/auth/gmail.compose https://www.googleapis.com/auth/gmail.readonly https://www.googleapis.com/auth/gmail.send https://www.googleapis.c
         * ▶
         * "
         * "exp" => "1734023526"
         * "expires_in" => "3599"
         * "email" => "ernandrewgregorio@gmail.com"
         * "email_verified" => "true"
         * "access_type" => "offline"
         * ]
         */
        $tokenInfoResponse = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'access_token' => $user->token,
        ]);

        $scopes = [];
        $expiresIn = 0;
        if ($tokenInfoResponse->ok()) {
            $expiresIn = $tokenInfoResponse->json()['expires_in']; // Token expiration time
            $grantedScopes = $tokenInfoResponse->json()['scope']; // Scopes granted by the user
            $scopes = implode(' ',explode(' ', $grantedScopes)); // Convert to array then concatenate with spaces
        }

        Auth::login(User::find(1));

        // updates the google_tokens table with the new token
        $client->setAccessToken([
            'access_token'=>$user->token,
            'expires_in'=>$expiresIn,
            'refresh_token'=>$user->refreshToken,
            'scope'=>$scopes,
            'token_type'=>'Bearer',
            'created'=>now()->timestamp,
        ]);
        return response()->json([
            'token' => $user->token,
            'refreshToken' => $user->refreshToken,
        ]);
    }

    // should be authenticated
    public function getEmails()
    {
        // Google Client automatically retrieves the google-token based from the logged-in user's id
        // this is done via Tomshaw's GoogleClient
        // We created a custom Gmail Service to handle the Gmail API
        $gmailService = new GmailService(app(GoogleClient::class));
        return response()->json(['messages' => $gmailService->getUserMessages()]);
    }
}
