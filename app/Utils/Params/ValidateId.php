<?php

namespace App\Utils\Params;

use Illuminate\Support\Facades\Validator;

class ValidateId
{
    public static function validate(mixed $id): int
    {
        $validData = (object) Validator::validate(
            ['id' => $id],
            [
                'id' => ['required', 'numeric']
            ]
        );
        return $validData->id;
    }
}
