<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use Carbon\Carbon;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class PastOrPresent implements iValidation {

    public function __construct(protected string|null $format = null){}

    public function validate($key, $value) {
        if ($this->format != null){
            $isDate = Carbon::createFromFormat($this->format, $value);
        }else{
            $isDate = new Carbon($value);
        }

        $Now = date('Y-m-d H:i:s');
        if (!$isDate->isSame($Now) && $isDate->isAfter($Now)){
            throw new BusinessException(sprintf(_("O campo %s deve ser uma data no passado ou presente"), _($key)));
        }
    }

}