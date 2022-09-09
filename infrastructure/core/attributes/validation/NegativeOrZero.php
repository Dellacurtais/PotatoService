<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class NegativeOrZero implements iValidation {

    public function validate($key, $value){
        if ($value > 0){
            throw new BusinessException(sprintf(_("O campo %s deve ser menor ou igual a zero"), _($key)));
        }
    }

}