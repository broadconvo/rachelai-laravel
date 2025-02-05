<?php

namespace App\Rules;

use App\Models\Broadconvo\PhoneExtension;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TenantPhoneExtensionExists implements ValidationRule
{
    protected string $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = PhoneExtension::whereTenantId($this->tenantId)
            ->where('extension_number', $value)
            ->exists();

        if (!$exists) {
            $fail("The phone extension does not exist in tenant #$this->tenantId.");
        }
    }
}
