<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Email implements iValidation {

    public function validate($key, $value){
        $Pattern = '[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})';
        $regex = '/^('.$Pattern.')$/u';
        if($value != '' && !preg_match($regex, $value, $b)){
            throw new BusinessException(sprintf(_("O campo %s deve ser um e-mail"), _($key)));
        }
    }

}