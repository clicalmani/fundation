<?php
namespace Clicalmani\Fundation\Mail;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * Class MailSMTP
 * 
 * @package Clialmani\Flesco
 * @author @Clicalmani\Fundation
 */
class MailSMTP extends Email
{
    /**
     * Mail object
     * 
     * @var \Symfony\Component\Mailer\Mailer
     */
    private $mailer;

    /**
     * DNS
     * 
     * @var string
     */
    private $dns;

    /**
     * DNS options
     * 
     * @var string[]
     */
    private $options = [];
    
    public function __construct()
    {
        $this->dns = 'smtp://' . env('MAIL_USERNAME', 'user') . ':' . 
                env('MAIL_PASSWORD', '') . '@' . 
                env('MAIL_HOST', 'localhost') . ':' . 
                env('MAIL_PORT', '465');
    }

    public function addOption(string $name, string $value)
    {
        $this->options[] = "$name=$value";
    }

    /**
     * @throws TransportExceptionInterface
     * @return void
     */
    public function send() : void
    {
        if ($this->options) $this->dns .= '?' . join('&', $this->options);

        $this->mailer = new Mailer(
            Transport::fromDsn($this->dns)
        );
        $this->mailer->send($this);
    }
}
