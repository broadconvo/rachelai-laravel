<?php

namespace App\Rules;

use App\Models\Broadconvo\UserAgent;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneExtensionNotUsed implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $inUse = UserAgent::where('extension_number', $value)
            ->exists();

        if ($inUse) {
            $fail("The {$attribute} is already in use by a another agent.");
        }
    }
}
