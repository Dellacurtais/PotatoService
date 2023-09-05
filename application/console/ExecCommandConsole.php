<?php

namespace application\console;

use infrastructure\core\interfaces\iConsole;

class ExecCommandConsole implements iConsole
{

    public function execute($args, $callback): void{
        $this->command($callback);
    }

    private function command($callback): void{
        logInfo('Enter the Command:');
        $Input = fgets(STDIN);

        if ($Input === "quit"){
            logInfo('-exec ended');
            return;
        }

        $args = explode(" ", trim($Input));

        if (count($args) && $args[0] !== '-exec'){
            $callback($args);
        }else{
            logError('Cant execute this command, sorry!');
        }

        logWarning('#### Enter with quit command to exit ####');
        $this->command($callback);
    }

}