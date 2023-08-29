<?php
namespace application\runner;

use application\console\CacheCommandConsole;
use application\console\HelpCommandConsole;
use infrastructure\core\interfaces\iConsole;
use infrastructure\core\interfaces\iRunner;

class Console implements iRunner {

    private $commands = [
        '-h' => HelpCommandConsole::class,
        '-cache' => CacheCommandConsole::class,
    ];

    public function main(): void{
        global $argc, $argv;
        array_shift($argv);

        if ($argc == 0){
            logError('Use -h to query available commands');
            return;
        }

        $command = array_shift($argv);
        if (!isset($this->commands[$command])){
            logError('Command '.$command.' does not exist');
            return;
        }

        $classRunner = new $this->commands[$command]();
        if (!($classRunner instanceof iConsole)){
            logError('Class '.$this->commands[$command].'does not implement iConsole interface');
            return;
        }
        $classRunner->execute($argv);
    }

    public function onStart(){  }

    public function afterDatabaseConnection(){  }

    public function onFinish(){  }

    public function onDetectNewVersion(){  }
}