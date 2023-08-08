<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnprocessableException extends Exception
{
    private $errors;

    public function __construct($errors)
    {
        parent::__construct('Invalid values');
        $this->errors = $errors;
    }

    public function render(Request $request): Response 
    {
        return response([
            'message' => $this->message,
            'errors' => $this->errors,
            'status'  => 422,
        ], 422);
    }
}
