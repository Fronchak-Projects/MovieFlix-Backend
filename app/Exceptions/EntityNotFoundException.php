<?php

namespace App\Exceptions;

class EntityNotFoundException extends ApiException
{
    public function __construct(string $message) 
    {
        parent::__construct($message, 404, 'Entity not found');
    }
}
