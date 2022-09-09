<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Url implements iValidation {

    public function validate($key, $value){
        if(!filter_var($value, FILTER_VALIDATE_URL)){
            throw new BusinessException(sprintf(_("O campo %s deve ser uma URL"), _($key)));
        }
    }

}