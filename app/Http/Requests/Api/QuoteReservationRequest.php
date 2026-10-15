<?php

namespace App\Http\Requests\Api;

use App\Http\Middleware\ResolveGym;
use App\Modules\Reservation\Validation\ReservationRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Where, when and how many: the first thing the reservation wizard checks.
 */
class QuoteReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ReservationRules::slot(ResolveGym::gym($this));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ReservationRules::messages(ResolveGym::locale($this));
    }
}
