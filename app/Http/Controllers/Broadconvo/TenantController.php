<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\Tenant;

class TenantController extends Controller
{
    public function create()
    {
        request()->validate([
            'name' => ['required', 'string'],
            'country' => ['required', 'string', 'exists:broadconvo.country,country_id'],
            'type' => ['required', 'integer']
        ]);

        Tenant::create([
            'tenant_id' => str()->uuid(),
            'tenant_name' => request('name'),
            'country_id' => request('country'),
            'tenant_type' => request('type'),
            'time_zone' => 'Asia/Hong_Kong',
            'subscription_tier' => 1,
            //'data_privacy' => true,
            //'block_anonymous' => true,
            //'logo_url'
            //'banner_url'
            //'hq_address'
            //'phone_number'

        ]);

        return response()->json(['message' => 'Successfully created Tenant']);
    }
}
