<?php

namespace Saia\Actas\controllers;

use Saia\Actas\controllers\algoespecifico\DecirHola;

class DecirHolaMundo
{
    public $message;

    public function __construct($message = 'mundo')
    {
        $this->message = $message;
    }

    public function decir()
    {
        $DecirHola = new DecirHola();
        return $DecirHola->decir() . $this->message;
        //return 'hola mundo 2';
    }
}
