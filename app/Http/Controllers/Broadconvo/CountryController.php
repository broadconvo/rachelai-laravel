<?php

namespace App\Http\Controllers\Broadconvo;

use App\Http\Controllers\Controller;
use App\Models\Broadconvo\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function create()
    {
        request()->validate([
            'name' => ['required', 'string'],
            'code' => ['required', 'string']
        ]);

        Country::create([
            'country_id' => str()->uuid(),
            'country_name' => request('name'),
            'phone_code' => request('code')
        ]);

        return response()->json(['message' => 'Successfully added Country']);
    }
}
