<?php
namespace infrastructure\core\general;

use infrastructure\core\traits\Singleton;

class OutputBuffer {

    use Singleton;

    private array $buffer;

    public function start(){
        ob_start();
    }

    public function returnAndClear(): string{
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    public function store(string $name = "Default"): void{
        $this->buffer[$name][] = ob_get_contents();
        ob_end_clean();
    }

    public function getBuffer(string $name, int $id = null): string {
        if ($id !== null)
            return $this->buffer[$name][$id];

        return $this->buffer[$name];
    }

    public function clear(string $name): void{
        unset($this->buffer[$name]);
    }

    public function reset(): void{
        $this->buffer = [];
    }

}