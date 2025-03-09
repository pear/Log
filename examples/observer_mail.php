<?php

require_once 'Log/observer.php';

class Log_observer_mail extends Log_observer
{
    private $to = '';
    private $subject = '';
    private $pattern = '';

    public function __construct($priority, $conf)
    {
        /* Call the base class constructor. */
        parent::__construct($priority);

        /* Configure the observer. */
        $this->to = $conf['to'];
        $this->subject = $conf['subject'];
        $this->pattern = $conf['pattern'];
    }

    public function notify($event): void
    {
        if (preg_match($this->pattern, $event['message']) != 0) {
            mail($this->to, $this->subject, $event['message']);
        }
    }
}

?>
