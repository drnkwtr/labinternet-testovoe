<?php

declare(strict_types=1);

namespace App\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractRequest extends FormRequest implements RequestInterface
{
    abstract public function toDto(): RequestDTOInterface;
}
