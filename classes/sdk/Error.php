<?php

namespace Revo\Sdk;

class Error extends \Exception
{

    public function __construct($data, \Exception $previous = null)
    {
        if($previous)
        {
            parent::__construct($previous->message, $previous->code, $previous);
        }
        else
        {
            parent::__construct($data->message, $data->status, null);
        }
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
