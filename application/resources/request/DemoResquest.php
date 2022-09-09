<?php

namespace application\resources\request;

use infrastructure\core\attributes\validation\MinLength;
use infrastructure\core\attributes\validation\NotBlank;
use infrastructure\core\general\MapRequest;

class DemoResquest extends MapRequest {

    #[MinLength(5)]
    public ?string $nome;

    #[NotBlank]
    public ?string $sobrenome;

}