<?php
namespace infrastructure\core\general;

use infrastructure\core\traits\Singleton;

class Smarty extends \Smarty {

    use Singleton;

    public $debug = false;

    public function __construct(){
        parent::__construct();
        $this->template_dir = $_ENV['SMARTY_DIR'];
        $this->compile_dir = $_ENV['SMARTY_CACHE'];
        if (!is_writable($this->compile_dir)) {
            @chmod($this->compile_dir, 0777);
        }

        if ($_ENV['SMARTY_TRIMSPACE']){
            try {
                $this->loadFilter('output', 'trimwhitespace');
            } catch (\SmartyException $ignore) {
            }
        }
    }

    /**
     * Set debug true or false
     * @param bool $debug
     */
    public function setDebug(bool $debug = true){
        $this->error_reporting = $debug;
        $this->error_unassigned = $debug;
    }

    /**
     * Load View
     */
    function view(string $template, array $data = [], bool $return = false): string|null {
        foreach ($data as $key => $val) {
            $this->assign($key, $val);
        }

        if ($return == false) {
            try {
                echo $this->fetch($template);
            }catch(\Exception $e){
                echo $e->getMessage();
            }
            return null;
        }else{
            try {
                return $this->fetch($template);
            }catch(\Exception $e){
                return $e->getMessage();
            }
        }
    }
}