<?php

namespace infrastructure\core\database;

use Illuminate\Database\Eloquent\Model;
use infrastructure\core\database\attributes\Column;
use infrastructure\core\interfaces\iValidation;

class EntityModel extends Model {

    public array $columns = [];
    public array $columnsName = [];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->execute();
    }

    /**
     * @var iValidation[]
     */
    private array $validations = [];

    public function execute(): void {
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
                    $this->columnsName[$columnName] = $propertyName;
                    if (!empty($propertyValue))
                        $this->setAttribute($columnName, $propertyValue);
                }
            }
        }

    }

    public function save(array $options = []): bool {
        $this->validate();
        return parent::save($options);
    }

    public function validate(): void {
        foreach ($this->validations as $column => $validation) {
            $validation->validate($column, $this->$column);
        }
    }

    public function toEntity(): static {
        foreach ($this->getAttributes() as $attribute => $value){
            if (isset($this->columnsName[$attribute])){
                $nameAttr = $this->columnsName[$attribute];
                $this->$nameAttr = $value;
            }
        }
        return $this;
    }
}