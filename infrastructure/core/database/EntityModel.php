<?php

namespace infrastructure\core\database;

use Illuminate\Database\Eloquent\Model;
use infrastructure\core\database\attributes\Column;
use infrastructure\core\interfaces\iValidation;

class EntityModel extends Model {

    public array $columns = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->execute();
    }

    /**
     * @var iValidation[]
     */
    private array $validations = [];

    public function execute(){
        $EntityClass = new \ReflectionClass($this);
        $AllProperties = $EntityClass->getProperties();

        foreach ($AllProperties as $property) {
            $Attributes = $property->getAttributes();
            $propertyName = $property->getName();
            $propertyValue = $this->$propertyName ?? null;

            foreach ($Attributes as $attribute){
                $attrInstance = $attribute->newInstance();

                if ($attrInstance instanceof iValidation){
                    $this->validations[$propertyName] = $attrInstance;
                }

                if ($attrInstance instanceof Column){
                    $columnName = $attrInstance->name;
                    if ($attrInstance->primaryKey){
                        $this->primaryKey = $columnName;
                    }

                    $this->columns[$propertyName] = $attrInstance;
                    if (!empty($propertyValue))
                        $this->setAttribute($propertyName, $propertyValue);
                }
            }
        }

    }

    public function validate(){
        foreach ($this->validations as $column => $validation) {
            $validation->validate($column, $this->$column);
        }
    }
}