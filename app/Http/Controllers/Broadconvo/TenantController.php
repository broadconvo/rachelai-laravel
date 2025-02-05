<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\PhoneExtension;
use App\Models\Broadconvo\Tenant;
use App\Models\Broadconvo\TenantPhone;
use App\Models\Broadconvo\TenantRachel;
use App\Models\Broadconvo\UserAgent;
use App\Models\Broadconvo\UserMaster;
use App\Models\User;
use App\Rules\PhoneExtensionNotExists;
use App\Rules\PhoneExtensionNotUsed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['country', 'phones', 'masters.userAgent', 'rachels'])->get();

        return response()->json($tenants);
    }
    public function create()
    {
        request()->validate([
            'name' => ['required', 'string'],
            'country' => ['required', 'string', 'exists:broadconvo.country,country_id'],
            'tenant_type' => ['required', 'integer'],
            'timezone' => ['string'],
            'subscription_tier' => ['integer'],
            'phone' => ['nullable', 'unique:broadconvo.tenant_phone,did_number'],
            'rachel.label' => ['required', 'string', 'max:255'],
            'rachel.type' => ['required', 'string', 'max:50'],
            'rachel.is_active' => ['boolean'],
            'rachel.functions' => ['array'],
            'rachel.functions.*' => ['string', 'min:2', 'max:50'],
            'user.firstname' => ['required', 'string', 'max:255'],
            'user.lastname' => ['required', 'string', 'max:255'],
            'user.email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:pgsql.users,email'],
            'user.username' => ['required', 'string', 'lowercase', 'max:255', 'unique:broadconvo.user_master,username'],
            'user.password' => ['required', Rules\Password::defaults()],
            'user.role' => ['required', 'string', 'max:255', 'in:supervisor,manager,agent'],
            'user.extension_number' => [
                'nullable', 'string', 'max:255',
                new PhoneExtensionNotExists(),
                new PhoneExtensionNotUsed()
            ],
            'picture_url' => ['url', 'string'],
        ]);

        $tenant = null;
        DB::connection('pgsql')->transaction(function () use (&$tenant) { // Postgres DB connection

            DB::connection('broadconvo')->transaction(function () use (&$tenant) {
                // create tenant
                $tenant = Tenant::create([
                    'tenant_id' => str()->uuid(),
                    'tenant_name' => request('name'),
                    'country_id' => request('country'),
                    'tenant_type' => request('tenant_type'),
                    'time_zone' => request('timezone'),
                    'subscription_tier' => request('subscription_tier'),
                    //'data_privacy' => true,
                    //'block_anonymous' => true,
                    //'logo_url'
                    //'banner_url'
                    //'hq_address'
                    //'phone_number'
                ]);

                if(!$tenant)
                    abort(403, 'Failed to create Tenant');

                /**
                 * Identify extension type via subscription tier.
                 * Tier - MaxLicense - ExtensionType - Name
                 * 0 -   1 - null   - Free
                 * 1 -  10 - 1      - Essentials
                 * 2 -  10 - 1,2    - Standard
                 * 3 -  25 - 1,2,3  - Premium
                 * 4 - 100 - null   - Enterprise
                 */

                if(request('user.extension_number')) {
                    $phoneExtension = PhoneExtension::updateOrCreate(
                        [ 'tenant_id' => $tenant->tenant_id, 'extension_number' => request('user.extension_number') ],
                        [
                            'tenant_id' => $tenant->tenant_id,
                            'extension_number' => request('user.extension_number'),
                            'extension_pwd' => request('password') ?? config('addwin.broadconvo.extension.password'),
                            'extension_type' => request('type') ?? request('subscription_tier'),
                        ]
                    );

                    if(!$phoneExtension)
                        abort(403, 'Failed to create phone extension');
                }

                // Create the user in the first database
                $user = User::create([
                    'name' => trim(request('user.firstname') . ' ' . request('user.lastname')),
                    'email' => request('user.email'),
                    'password' => Hash::make(request('user.password')),
                ]);

                // Create the user in the second database (UserMaster)
                $broadconvoUserMaster = UserMaster::create([
                    'username' => request('user.username'),
                    'email' => request('user.email'),
                    'pwd_hash' => crypt(request('user.password'), "$1$".md5(uniqid(rand(), true))),
                    'first_name' => request('user.firstname'),
                    'last_name' => request('user.lastname'),
                    'profile_pic_url' => request('user.picture_url'),
                    'is_active' => true,
                    'is_super_admin' => false,
                    'added_on' => now(),
                ]);

                // Create the UserAgent instance
                $broadconvoUserAgent = new UserAgent([
                    'time_zone' => config('app.timezone'),
                    'tenant_id' => $tenant->tenant_id,
                    'agent_role' => request('user.role'),
                    'extension_number' => request('user.extension_number'),
                    'added_on' => now(),
                ]);

                // Save the UserAgent using the relationship
                $broadconvoUserMaster->userAgent()->save($broadconvoUserAgent);

                if(!$user)
                    abort(403, 'Failed to create user');

                // Create Rachel in rachel_tenant table
                $rachel = TenantRachel::create([
                    'rachel_id' => str()->uuid(),
                    'tenant_id' => $tenant->tenant_id,
                    'rachel_label' => request('rachel.label'),
                    'rachel_type' => request('rachel.type'),
                    'is_active' => request('rachel.is_active') ?? true,
                    'rachel_functions' => json_encode(request('rachel.functions')),
                ]);

                if(!$rachel)
                    abort(403, 'Failed to create Rachel');

                // Create DID for tenant with Rachel
                if(request('phone')) {
                    $tenantPhone = TenantPhone::create([
                        'tenant_id' => $tenant->tenant_id,
                        'did_number' => request('phone'),
                        'agent_id' => $broadconvoUserAgent->agent_id,
                        'rachel_id' => $rachel->rachel_id
                    ]);

                    if(!$tenantPhone)
                        abort(403, 'Failed to create Direct Inward Dialling (DID) for tenant');

                }

            }); // Broadconvo DB connection

        });

        $tenant->load(['country', 'masters.userAgent', 'phones', 'rachels', 'knowledgebases.entries']);

        return response()->json([
            'message' => 'Successfully created Tenant with Rachel and DID',
            'data' => $tenant
        ]);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['country', 'masters.userAgent', 'phones', 'rachels', 'knowledgebases.entries']);

        return response()->json($tenant);
    }
}
