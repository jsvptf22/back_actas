<?php

namespace Saia\Actas\controllers;

use Saia\controllers\IcsController;
use Saia\Actas\formatos\acta\FtActa;
use Saia\controllers\SessionController;
use Saia\controllers\SendMailController;

class ActaMailInvitation
{

    /**
     * almacena la instancia del formato
     *
     * @var FtActa
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    protected $FtActa;

    public function __construct(FtActa $FtActa)
    {
        $this->FtActa = $FtActa;
    }

    /**
     * genera el archivo ics de la reunion
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function generateIcsFile()
    {
        global $rootPath;

        $ActPlanning = $this->FtActa->ActPlanning;

        $DateInterval = new \DateInterval('PT1H');
        $DateTime = new \DateTime($ActPlanning->date);
        $DateTime->add($DateInterval);

        $properties = [
            'description' => $ActPlanning->subject,
            'dtstart' => $ActPlanning->date,
            'dtend' => $DateTime->format('Y-m-d H:i:s'),
            'summary' => $ActPlanning->subject,
            'organizer' => SessionController::getValue('email')
        ];

        $ics = new IcsController($properties);
        $content = $ics->to_string();

        $icsRoute = $rootPath . SessionController::getTemporalDir() . '/invitacion.ics';

        if (!file_put_contents($icsRoute, $content)) {
            throw new \Exception("Error al generar la invitacion", 1);
        }

        return $icsRoute;
    }

    /**
     * genera el cuerpo del correo
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function generateBody()
    {
        $roomName = $this->FtActa->getRoom();
        $roomRoute = "https://asker-jsv.herokuapp.com/room.html?room={$roomName}";
        return "Para ingresar a la sala de interacción con la toma de decisiones haga click <a href='{$roomRoute}' >aquí</a>";
    }

    /**
     * ejecuta el envio del correo
     *
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function send()
    {
        $icsRoute = $this->generateIcsFile();
        $body = $this->generateBody();

        $SendMailController = new SendMailController('Invitación a reunión', $body);
        $SendMailController->setDestinations(
            SendMailController::DESTINATION_TYPE_EMAIL,
            $this->FtActa->getAssistantsEmail()
        );
        $SendMailController->setAttachments(
            SendMailController::ATTACHMENT_TYPE_ROUTE,
            [$icsRoute]
        );
        $SendMailController->send();
    }
}
