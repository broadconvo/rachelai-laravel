<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\PhoneExtension;
use App\Models\Broadconvo\UserAgent;
use App\Rules\TenantPhoneExtensionExists;

class PhoneExtensionController extends Controller
{
    // get available extension number that is not being assigned to any agents yet
    public function available()
    {
        $phoneExtensions = PhoneExtension::whereNotIn(
            'extension_number',
            UserAgent::select('extension_number')->pluck('extension_number')
        )->get();

        return response()->json($phoneExtensions);
    }

    public function create()
    {
        request()->validate([
            'number' => ['required', 'string', 'max:255', 'unique:broadconvo.extension_def,extension_number'],
            'password' => ['required', 'string'],
            'type' => ['required', 'integer'],
            'tenant_id' => ['required', 'exists:broadconvo.tenant,tenant_id'],
            'for_queue' => ['boolean']
        ]);

        $phoneExtension = PhoneExtension::create([
            'extension_number' => request('number'),
            'extension_pwd' => request('password'),
            'extension_type' => request('type'),
            'tenant_id' => request('tenant_id'),
        ]);

        if(!$phoneExtension)
            abort(403, 'Failed to create Phone Extension');

        return response()->json(['message' => 'Successfully created Phone Extension']);
    }
}
