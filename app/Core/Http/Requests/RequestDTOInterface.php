<?php

declare(strict_types=1);

namespace App\Core\Http\Requests;

use Illuminate\Http\Request;

interface RequestDTOInterface
{
    public static function fromRequest(Request $request): self;
}
