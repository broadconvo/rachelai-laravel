<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\TenantPhone;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function upsert()
    {
        request()->validate([
            'tenant_id' => ['required', 'string', 'exists:broadconvo.tenant,tenant_id'],
            'phone' => ['required', 'string', 'max:50'],
            'agent_id' => ['required', 'string', 'exists:broadconvo.user_agent,agent_id'],
            'rachel_id' => ['string', 'exists:broadconvo.rachel_tenant,rachel_id', 'nullable'],
        ]);


        $phone = TenantPhone::updateOrCreate(
            [
                'did_number' => request('phone'),
                'tenant_id' => request('tenant_id')
            ],
            [
                'tenant_id' => request('tenant_id'),
                'did_number' => request('phone'),
                'agent_id' => request('agent_id'),
                'rachel_id' => request('rachel_id') ?? null,
            ]
        );

        if(!$phone)
            abort(422, 'Failed to add phone number for tenant.');

        return response()->json([
            'message' => 'Successfully processed your tenant. Note: Agent-id is the owner.',
            'data' => $phone
        ]);
    }
}
