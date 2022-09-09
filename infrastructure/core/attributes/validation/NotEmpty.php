<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class NotEmpty implements iValidation {

    public function validate($key, $value){
        if (empty($value)){
            throw new BusinessException(sprintf(_("O campo %s não pode estar vazio"), _($key)));
        }
    }

}