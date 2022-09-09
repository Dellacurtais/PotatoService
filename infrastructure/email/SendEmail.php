<?php
namespace infrastructure\email;

use infrastructure\core\traits\Singleton;

class SendEmail {

    use Singleton;

    protected $config = array();
    protected $BodyEmail = "";
    protected $email = array();
    protected $copyEmail = array();
    protected $from = array();
    protected $subject = "";

    public function __construct(){
        $this->initConfig();
    }

    public function initConfig(): void{
        $this->config = [
            'protocol' => 'smtp',
            'smtp_host' => $_ENV["smtp_host"],
            'smtp_port' => $_ENV["smtp_port"],
            'smtp_user' => $_ENV["smtp_user"],
            'smtp_pass' => $_ENV["smtp_pass"],
            'mailtype'  => 'html',
            'charset'   => 'utf-8'
        ];

        $this->from['email'] = $_ENV["smtp_user"];
        $this->from['nome'] = $_ENV["smtp_name"];
    }

    public function setBody(string $body, array $args = []): void{
        $this->BodyEmail = $body;
        if (is_array($args) && count($args) > 0) {
            $GetArgs = array_keys($args);
            foreach ($GetArgs as $k => $arg) {
                $GetArgs[$k] = "{" . $arg . "}";
            }
            $GetVals = array_values($args);
            $this->BodyEmail = str_replace($GetArgs, $GetVals, $this->BodyEmail);
        }
    }

    public function setFrom(string $email, string $name): void{
        $this->from['email'] = $email;
        $this->from['nome'] = $name;
    }

    public function setSubject(string $subject): void{
        $this->subject = $subject;
    }

    public function setEmail(string $email): void{
        if (is_array($email)){
            foreach ($email as $value){
                $this->email[] = $value;
            }
        }else{
            $this->email[] = $email;
        }
    }

    public function setCopyEmail($email): void{
        if (is_array($email)){
            foreach ($email as $value){
                $this->copyEmail[] = $value;
            }
        }else{
            $this->copyEmail[] = $email;
        }
    }

    public function sendMail(): bool{
        $Email = new EmailHelper($this->config);
        $Email->from($this->from['email'], $this->from['nome']);
        foreach ($this->email as $email) {
            $Email->to($email);
        }
        foreach ($this->copyEmail as $d => $mail) {
            $Email->cc($mail);
        }
        $Email->subject($this->subject);
        $Email->message($this->BodyEmail);
        return $Email->send();
    }

}