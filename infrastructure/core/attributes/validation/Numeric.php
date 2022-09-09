<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Numeric implements iValidation {

    public function validate($key, $value){
        if (!is_numeric($value)){
            throw new BusinessException(sprintf(_("O campo %s deve ser numérico"), _($key)));
        }
    }

}