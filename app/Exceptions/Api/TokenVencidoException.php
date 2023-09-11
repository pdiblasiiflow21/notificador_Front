<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Exception;

class TokenVencidoException extends Exception
{
    public function __construct($message = '', $code = 401, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
