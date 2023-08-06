<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiException extends Exception
{
    private int $status;
    private string $error;

    public function __construct(string $message, int $status, string $error) 
    {
        parent::__construct($message);
        $this->status = $status;
        $this->error = $error;
    }

    public final function getStatus(): int 
    {
        return $this->status;
    }

    public final function getError(): string 
    {
        return $this->error;
    }

    public function render(Request $request): Response 
    {
        return response([
            'error' => $this->error,
            'message' => $this->message,
            'status' => $this->status
        ], $this->status);
    }
}
