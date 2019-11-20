<?php

namespace Saia\Actas\controllers\algoespecifico;

class DecirHola
{
    public $message;

    public function __construct($message = 'Hola 222222222')
    {
        $this->message = $message;
    }

    public function decir()
    {
        return $this->message;
    }
}
