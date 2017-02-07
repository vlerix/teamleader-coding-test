<?php

namespace App\Exceptions;

use Exception;

class DiscountException extends Exception
{
	
    public function __construct($message, $code = 0, Exception $previous = null) {

        parent::__construct($message, $code, $previous);

    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}";
    }
}