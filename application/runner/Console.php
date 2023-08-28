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

        if ($argc == 0){
            echo _('Use -h to query available commands');
            return;
        }
        $command = $argv[1];
        if (!isset($this->commands[$command])){
            echo _('Command '.$command.' does not exist');
            return;
        }

        $classRunner = new $this->commands[$command]();
        if (!($classRunner instanceof iConsole)){
            echo _('Class '.$this->commands[$command].'does not implement iConsole interface');
            return;
        }
        unset($argv[0], $argv[1]);
        $classRunner->execute($argv);
    }

    public function onStart(){  }

    public function afterDatabaseConnection(){  }

    public function onFinish(){  }

    public function onDetectNewVersion(){  }
}