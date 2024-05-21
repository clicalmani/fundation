<?php
namespace Clicalmani\Fundation\Mail;

/**
 * Class MailSMTP
 * 
 * @package Clialmani\Flesco
 * @author @Clicalmani\Fundation
 */
class MailSMTP 
{
    private $WordWrap;
    
    public function __construct(private $mail = null)
    {
        $this->mail             = new \PHPMailer\PHPMailer\PHPMailer;
        
        $this->mail->CharSet    = env('MAIL_CHARSET', 'UTF-8');
        $this->mail->Encoding   = env('MAIL_ENCODING', 'base64');
        $this->mail->Host       = env('MAIL_HOST', 'localhost');
        $this->mail->Username   = env('MAIL_USERNAME', 'user');
        $this->mail->Password   = env('MAIL_PASSWORD', '');
        $this->mail->SMTPSecure = env('MAIL_ENCRYPTION', 'ssl');
        $this->mail->Port       = env('MAIL_PORT', '465'); 

        $this->mail->isSMTP(true);

        $this->mail->SMTPAuth   = true;
        $this->WordWrap         = 50;
    }

    /**
     * Set body
     * 
     * @param string $body
     * @return void
     */
    public function setBody(string $body) : void
    {
        $this->mail->Body = $body;
    }

    /**
     * Set subject
     * 
     * @param string $subject
     * @return void
     */
    public function setSubject(string $subject) : void
    {
        $this->mail->Subject = $subject;
    }

    /**
     * Set word wrap
     * 
     * @param int $WordWrap
     * @return void
     */
    public function setWordWrap(int $WordWrap) : void
    {
        $this->mail->WordWrap = $WordWrap;
    }

    /**
     * PHP magic __call
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args) : mixed
    {
        $mailer_methods = [
            'setFrom',
            'addAddress',
            'addAttachment',
            'addStringAttachment',
            'addEmbeddedImage',
            'addCustomHeader',
            'addCC',
            'addBC',
            'isHTML',
            'send'
        ];

        if ( in_array($method, $mailer_methods) ) {
            return $this->mail->{$method}(...$args);
        } else throw new \Clicalmani\Fundation\Exceptions\MailException("Unsupported method $method has been called on " . static::class);
    }
}
