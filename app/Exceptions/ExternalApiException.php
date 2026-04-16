<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ExternalApiException extends Exception
{
    //

    public function __construct(
        protected string $apiName,
        string $message='',
        int $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getApiName(): string
    {
        return $this->apiName;
    }

    public function toResponse(): array
    {
        return [
            'status'    => 'error',
            'message'   => "$this->apiName returned an invalid response",
        ];
    }
}
