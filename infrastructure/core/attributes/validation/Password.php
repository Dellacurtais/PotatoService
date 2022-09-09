<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class Password implements iValidation {

    public function __construct(protected int $min){}

    public function validate($key, $value){
        $uppercase = preg_match('@[A-Z]@', $value);
        $lowercase = preg_match('@[a-z]@', $value);
        $number    = preg_match('@[0-9]@', $value);
        $specialChars = preg_match('@[^\w]@', $value);
        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($value) < $this->min) {
            throw new BusinessException(_("Senha deve conter um número, letra maiúscula e miniúscula e um caracter especial"));
        }
    }

}