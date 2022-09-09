<?php

namespace infrastructure\core\general;

use Illuminate\Support\Facades\DB;

abstract class GeneralReflection {

    public function __construct(){
        $Reflection = new \ReflectionClass(get_called_class());
        $properties = $Reflection->getProperties();
        reflection_properties($this, $properties);
    }

}