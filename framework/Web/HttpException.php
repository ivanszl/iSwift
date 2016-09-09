<?php
namespace iSwift\Web;

use Exception;

class HttpException extends Exception
{
    public $statusCode;

    public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }

    public function getName()
    {
        if (isset(Output::$httpStatuses[$this->statusCode])) {
            return Output::$httpStatuses[$this->statusCode];
        } else {
            return 'Error';
        }
    }
}
