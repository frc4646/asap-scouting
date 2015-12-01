<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.1.0
 */

namespace app\Mail;

class Mailer
{
    protected $view;

    protected $mailer;

    public function __construct($view, $mailer)
    {
        $this->view = $view;
        $this->mailer = $mailer;
    }

    public function send($template, $data, $callback)
    {
        $message = new Message($this->mailer);

        $this->view->appendData($data);

        $message->body($this->view->render($template));

        call_user_func($callback, $message);

        $this->mailer->send();
    }
}
