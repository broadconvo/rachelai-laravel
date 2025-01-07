<?php

namespace App\Rules;

use App\Models\Broadconvo\PhoneExtension;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExtensionExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = PhoneExtension::where('extension_number', $value)->exists();

        if (!$exists) {
            $fail("The {$attribute} does not exist.");
        }
    }
}
