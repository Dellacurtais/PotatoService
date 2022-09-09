<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class NotBlank implements iValidation {

    public function validate($key, $value){
        if (empty(trim($value))){
            throw new BusinessException(sprintf(_("O campo %s não pode estar em branco"), _($key)));
        }
    }

}