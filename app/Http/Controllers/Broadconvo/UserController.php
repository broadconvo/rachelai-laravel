<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\UserAgent;
use App\Models\Broadconvo\UserMaster;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $agents = UserMaster::with('userAgent')
            ->orderBy('added_on', 'desc')
            ->get();

        return response()->json($agents);
    }
}
