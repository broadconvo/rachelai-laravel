<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\Tenant;
use App\Models\Broadconvo\TenantPhone;
use App\Models\Broadconvo\TenantRachel;
use App\Models\Broadconvo\UserMaster;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['country', 'phones', 'userAgents'])->get();

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
            'rachel.label' => ['required', 'string', 'max:255'],
            'rachel.type' => ['required', 'string', 'max:50'],
            'rachel.is_active' => ['boolean'],
            'rachel.functions' => ['array'],
            'rachel.functions.*' => ['string', 'min:2', 'max:50']
        ]);

        DB::connection('broadconvo')->transaction(function () {
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

            $user = UserMaster::whereEmail(request('owner_email'))->first();

            if(!$user)
                abort(403, 'User does not exist');

            // Create DID for tenant with Rachel
            $tenantPhone = TenantPhone::create([
                'tenant_id' => $tenant->tenant_id,
                'did_number' => request('phone'),
                'agent_id' => $user->userAgent->agent_id,
                'rachel_id' => $rachel->rachel_id
            ]);

            if(!$tenantPhone)
                abort(403, 'Failed to create Direct Inward Dialling (DID) for tenant');
        }); // DB connection

        return response()->json(['message' => 'Successfully created Tenant with Rachel and DID']);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['country', 'masters.userAgent', 'phones', 'rachels', 'knowledgebases']);

        return response()->json($tenant);
    }
}
