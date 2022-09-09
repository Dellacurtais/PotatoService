<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Pattern implements iValidation {

    public function __construct(protected string $pattern){}

    public function validate($key, $value){
        $regex = '/^('.$this->pattern.')$/u';
        if($value != '' && !preg_match($regex, $value)){
            throw new BusinessException(sprintf(_("O campo %s é inválido"), _($key)));
        }
    }

}