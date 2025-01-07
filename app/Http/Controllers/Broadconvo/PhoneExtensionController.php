<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\PhoneExtension;
use App\Models\Broadconvo\UserAgent;

class PhoneExtensionController extends Controller
{
    public function available()
    {
        $phoneExtensions = PhoneExtension::whereNotIn(
            'extension_number',
            UserAgent::select('extension_number')->pluck('extension_number')
        )->get();

        return response()->json($phoneExtensions);
    }
}
