<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Positive implements iValidation {

    public function validate($key, $value){
        if ($value <= 0){
            throw new BusinessException(sprintf(_("O campo %s deve ser maior que zero"), _($key)));
        }
    }

}