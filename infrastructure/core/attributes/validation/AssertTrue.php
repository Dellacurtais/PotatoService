<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class AssertTrue implements iValidation {

    public function validate($key, $value) {
        if ($value !== true){
            throw new BusinessException(sprintf(_("O campo %s é inválido"), _($key)));
        }
    }

}