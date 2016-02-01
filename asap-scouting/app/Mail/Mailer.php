<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.3.0
 */

namespace app\Mail;

use PHPMailer;

class Mailer
{
    protected $view;

    protected $mailer;

    /**
     * Class Constructor
     *
     * @param object $view   View Object
     * @param object $mailer PHPMailer Instance
     */
    public function __construct($view, PHPMailer $mailer)
    {
        $this->view = $view;
        $this->mailer = $mailer;
    }

    /**
     * Sends an email with the particular view rendered
     *
     * @param string   $template View to render
     * @param array    $data     Additonal Data to send to the view
     * @param function $callback callback function
     */
    public function send($template, $data, $callback)
    {
        $message = new Message($this->mailer);

        $this->view->appendData($data);

        $message->body($this->view->render($template));

        call_user_func($callback, $message);

        $this->mailer->send();
    }
}
