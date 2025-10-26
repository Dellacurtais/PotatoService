<?php

namespace infrastructure\core\general;

use Illuminate\Support\Facades\DB;

abstract class GeneralReflection {

    public function __construct(){
        $Reflection = ReflectionCache::get(get_called_class());
        reflection_properties($this, $Reflection['properties']);
    }

}