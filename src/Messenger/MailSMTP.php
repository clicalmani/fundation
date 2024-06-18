<?php
namespace Clicalmani\Fundation\Messenger;

/**
 * Class MailSMTP
 * 
 * @package Clialmani\Flesco
 * @author @Clicalmani\Flesco
 * 
 * @method bool setFrom(string $address, string $name = '', ?bool $auto = true)
 * @method bool addAddress(string $address, ?string $name = '')
 * @method bool addAttachment(string $path, ?string $name = '', ?string $encoding = self::ENCODING_BASE64, ?string $type = '', ?string $disposition = 'attachment')
 * @method bool addStringAttachment(string $string, string $filename, ?string $encoding = self::ENCODING_BASE64, ?string $type = '', ?string $disposition = 'attachment')
 * @method bool addEmbeddedImage(string $path, string $cid, ?string $name = '', ?string $encoding = self::ENCODING_BASE64, ?string $type = '', ?string $disposition = 'inline')
 * @method bool addCustomHeader(string $name, ?string $value = null)
 * @method bool addCC(string $address, ?string $name = '')
 * @method bool addBCC(string $address, ?string $name = '')
 * @method void clearAddresses()
 * @method void isHTML(?bool $isHtml = true)
 * @method bool send()
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
            'clearAddresses',
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
