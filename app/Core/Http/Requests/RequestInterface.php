<?php

declare(strict_types=1);

namespace App\Core\Http\Requests;

interface RequestInterface
{
    public function toDto(): RequestDTOInterface;
}
