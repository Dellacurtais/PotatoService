<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class MinLength implements iValidation {

    public function __construct(protected string $size){}

    public function validate($key, $value){
        if (is_null($value) || strlen($value) < $this->size){
            throw new BusinessException(sprintf(_("O campo %s deve conter mais de %d caracteres"), _($key), $this->size));
        }
    }

}