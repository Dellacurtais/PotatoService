<?php
namespace infrastructure\core\general;

use infrastructure\core\traits\Singleton;

class Session {

    use Singleton;

    public function __construct(){
        session_name($_ENV["SESSION_ID"]);
        session_start();
    }

    public function setFlash(string $key, string $message): void{
        $Flash = $this->get("flash_data");

        if (isset($Flash[$key]))
            unset($Flash[$key]);

        $data = array_merge( is_array($Flash) ? $Flash : [] , [ $key => $message ] );

        $this->set("flash_data", $data);
    }

    public function getFlash(string $key): ?string{
        $FlashData = $this->get("flash_data");
        $Return = $FlashData[$key] ?? null;
        if (isset($FlashData[$key])) {
            unset($FlashData[$key]);
            $this->set("flash_data", $FlashData);
        }
        return $Return;
    }

    public function get(string $key, mixed $default = null){
        return $this->exists($key) ? $_SESSION[$key] : $default;
    }

    public function set(string $key, mixed $value): Session {
        $_SESSION[$key] = $value;
        return $this;
    }

    public function merge(string $key, mixed $value): Session {
        if (is_array($value) && is_array($old = $this->get($key))) {
            $value = array_merge_recursive($old, $value);
        }
        return $this->set($key, $value);
    }

    public function delete(string $key): Session {
        if ($this->exists($key)) {
            unset($_SESSION[$key]);
        }
        return $this;
    }

    public function clear(): Session{
        $_SESSION = [];
        return $this;
    }

    public function exists(string $key): bool{
        return array_key_exists($key, $_SESSION);
    }

    public static function id(bool $new = false): string|bool {
        if ($new && session_id()) {
            session_regenerate_id(true);
        }
        return session_id() ?: '';
    }

    public function destroy(): void {
        if (self::id()) {
            session_unset();
            session_destroy();
            session_write_close();
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 4200,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
        }
    }

    public function __get($key) {
        return $this->get($key);
    }

    public function __set($key, $value) {
        $this->set($key, $value);
    }

    public function __unset($key){
        $this->delete($key);
    }

    public function __isset($key) {
        return $this->exists($key);
    }

    public function count(): int {
        return count($_SESSION);
    }

    /**
     * Retrieve an external Iterator.
     *
     * @return \Traversable
     */
    public function getIterator(){
        return new \ArrayIterator($_SESSION);
    }

    /**
     * Whether an array offset exists.
     *
     * @param mixed $offset
     *
     * @return boolean
     */
    public function offsetExists($offset){
        return $this->exists($offset);
    }

    /**
     * Retrieve value by offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset){
        return $this->get($offset);
    }

    /**
     * Set a value by offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value){
        $this->set($offset, $value);
    }

    /**
     * Remove a value by offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset){
        $this->delete($offset);
    }
}