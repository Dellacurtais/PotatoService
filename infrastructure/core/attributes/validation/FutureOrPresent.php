<?php

namespace infrastructure\core\attributes\validation;

use Attribute;
use Carbon\Carbon;
use infrastructure\core\exception\BusinessException;
use infrastructure\core\interfaces\iValidation;

#[attribute(Attribute::TARGET_PROPERTY)]
class FutureOrPresent implements iValidation {

    public function __construct(protected string|null $format = null){}

    public function validate($key, $value){
        $timezone = new \DateTimeZone($_ENV['TIMEZONE']);
        if ($this->format != null){
            $isDate = Carbon::createFromFormat($this->format, $value, $timezone);
        }else{
            $isDate = new Carbon($value, $timezone);
        }

        $Now = Carbon::createFromFormat($this->format, date($this->format), $timezone);
        if (!$isDate->eq($Now) && $isDate->isBefore($Now)){
            throw new BusinessException(sprintf(_("O campo %s não pode ser no passado"), _($key)));
        }
    }

}