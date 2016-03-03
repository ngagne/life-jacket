<?php

namespace ATC;

/**
 * Class Mailer
 * @package ATC
 */
class Mailer
{
    protected $mailer;

    /**
     * Mailer constructor.
     */
    public function __construct() {
        $config = Config::getInstance();
        switch ($config->mail_transport) {
            case 'smtp':
                $transport = \Swift_SmtpTransport::newInstance($config->mail_smtp_host, $config->mail_smtp_port)
                    ->setUsername($config->mail_smtp_username)
                    ->setPassword($config->mail_smtp_password);
                break;
            case 'sendmail':
                $transport = \Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
                break;
            case 'mail':
            default:
            $transport = \Swift_MailTransport::newInstance();
                break;
        }
        $this->mailer = \Swift_Mailer::newInstance($transport);
    }

    /**
     * Sends an email from the application
     *
     * @param string $content
     * @param string $template
     * @return int
     */
    public function sendSystemMessage($content, $template = 'index') {
        $config = Config::getInstance();

        $recipients = explode(',', $config->system_mail_to);
        $fromName = $config->system_mail_from_name;
        $fromEmail = $config->system_mail_from_email;
        $subject = $config->system_mail_subject;

        // load template
        $file = APPLICATION_PATH . '/layouts/emails/' . $template . '.html';
        if (!file_exists($file)) {
            $file = APPLICATION_PATH . '/layouts/emails/index.html';
        }
        $template = file_get_contents($file);

        // replace with submitted data
        $body = str_replace('[[__content]]', $content, $template);

        // build email
        $message = \Swift_Message::newInstance($subject)
            ->setFrom(array($fromEmail => $fromName))
            ->setTo($recipients)
            ->setBody($body, 'text/html')
            ->addPart(strip_tags($body), 'text/plain');

        // send message
        return $this->mailer->send($message);
    }
}