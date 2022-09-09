<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Decimal implements iValidation {

    public function validate($key, $value){
        $Pattern = '\d+(\.\d+)';
        $regex = '/^('.$Pattern.')$/u';
        if($value != '' && !preg_match($regex, $value, $b)){
            throw new BusinessException(sprintf(_("O campo %s deve ser um decimal"), _($key)));
        }
    }

}