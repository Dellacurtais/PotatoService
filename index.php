<?php
define("INIT_REQUEST", microtime(true));
const ROOT_PATH = __DIR__;
const ENV = "dev"; //prod, dev

include "./infrastructure/bootstrap.php";
