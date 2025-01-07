<?php

namespace App\Rules;

use App\Models\Broadconvo\PhoneExtension;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueExtensionNumber implements ValidationRule
{
    protected string $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the extension_number exists for the given tenant_id
        $exists = PhoneExtension::where('extension_number', $value)
            ->where('tenant_id', $this->tenantId)
            ->exists();

        if ($exists) {
            $fail('The :attribute is already taken for this tenant.');
        }
    }
}
