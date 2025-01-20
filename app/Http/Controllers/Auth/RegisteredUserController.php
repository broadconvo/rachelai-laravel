<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\PhoneExtension;
use App\Models\Broadconvo\UserAgent;
use App\Models\Broadconvo\UserMaster;
use App\Models\User;
use App\Rules\PhoneExtensionExists;
use App\Rules\PhoneExtensionNotUsed;
use App\Rules\PhoneExtensionUnique;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:'.UserMaster::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'max:255', 'in:supervisor,manager,agent'],
            'tenant_id' => ['required', 'string', 'max:255'],
            'extension_number' => [
                'required', 'string', 'max:255',
                new PhoneExtensionExists($request->tenant_id),
                new PhoneExtensionNotUsed()
            ],
            'picture_url' => ['url', 'string'],
        ]);

        // Start a database transaction for both DBs
        DB::connection('pgsql')->transaction(function () use ($request) {
            DB::connection('broadconvo')->transaction(function () use ($request) {
                // Create the user in the first database
                $user = User::create([
                    'name' => trim($request->firstname . ' ' . $request->lastname),
                    'email' => $request->email,
                    'password' => Hash::make($request->string('password')),
                ]);

                // Create the user in the second database (UserMaster)
                $broadconvoUserMaster = UserMaster::create([
                    'username' => $request->username,
                    'email' => $request->email,
                    'pwd_hash' => crypt($request->string('password'), "$1$".md5(uniqid(rand(), true))),
                    'first_name' => $request->firstname,
                    'last_name' => $request->lastname,
                    'profile_pic_url' => $request->picture_url,
                    'is_active' => true,
                    'is_super_admin' => false,
                    'added_on' => now(),
                ]);

                // Create the UserAgent instance
                $broadconvoUserAgent = new UserAgent([
                    'time_zone' => config('app.timezone'),
                    'tenant_id' => $request->tenant_id,
                    'agent_role' => $request->role,
                    'extension_number' => $request->extension_number,
                    'added_on' => now(),
                ]);

                // Save the UserAgent using the relationship
                $broadconvoUserMaster->userAgent()->save($broadconvoUserAgent);

                // Trigger the registered event and login the user
                event(new Registered($user));
                Auth::login($user);
            });
        });
        return response()->noContent();
    }
}
