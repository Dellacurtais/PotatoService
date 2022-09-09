<?php
namespace infrastructure\core\general;

class Password {

    protected static $cost = 10;

    public static function checkCost(){
        $timeTarget = 0.05;
        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("teste", PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);
        echo "Appropriate Cost Found: " . $cost;
    }
    
    public static function getPassword(string $passworld): bool|string{
        return password_hash($passworld, PASSWORD_BCRYPT, [
            "cost" => self::$cost
        ]);
    }

    public static function verifyPassword(string $passworld, string $hash): bool{
        return password_verify($passworld, $hash);
    }
}