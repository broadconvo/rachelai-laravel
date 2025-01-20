<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\Rachel;
use Illuminate\Http\Request;

class RachelController extends Controller
{
    public function create()
    {
        request()->validate([
            'tenant_id' => ['required', 'string', 'exists:broadconvo.tenant,tenant_id'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'functions' => ['array'],
            'functions.*' => ['string', 'min:2', 'max:50']
        ]);

        Rachel::create([
            'rachel_id' => str()->uuid(),
            'tenant_id' => request('tenant_id'),
            'rachel_label' => request('label'),
            'rachel_type' => request('type'),
            'is_active' => request('is_active') ?? true,
            'rachel_functions' => json_encode(request('functions')),
        ]);

        return response()->json(['message' => 'Successfully created RachelAI']);
    }
}
