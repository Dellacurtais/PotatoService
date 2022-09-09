<?php
namespace infrastructure\core\exception;
use Throwable;

class ServerException extends \Exception{

    public $error_message = "ServerException";

    public function __construct($message = "", $code = 500, Throwable $previous = null){
        $message = empty($message) ? $this->getClassName() : $message;
        parent::__construct(_($message), $code, $previous);
    }

    private function getClassName(){
        $classMap = explode("\\", get_called_class());
        return end($classMap);
    }

}