<?php

namespace Saia\Actas\controllers;

use Saia\Actas\formatos\acta\FtActa;
use Saia\controllers\IcsController;
use Saia\controllers\SendMailController;
use Saia\controllers\SessionController;
use Saia\models\vistas\VfuncionarioDc;

class MeetMailInvitation
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
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function generateIcsFile()
    {
        global $rootPath;

        $FtAgendamientoActa = $this->FtActa->FtAgendamientoActa;

        $DateInterval = new \DateInterval("PT{$FtAgendamientoActa->duration}M");
        $DateTime = new \DateTime($FtAgendamientoActa->date);
        $DateTime->add($DateInterval);

        $properties = [
            'description' => $this->getIcsDescription($FtAgendamientoActa),
            'dtstart' => $FtAgendamientoActa->date,
            'dtend' => $DateTime->format('Y-m-d H:i:s'),
            'summary' => $FtAgendamientoActa->subject,
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
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function generateBody()
    {
        $roomRoute = $this->getRoomRoute();

        $FtAgendamientoActa = $this->FtActa->FtAgendamientoActa;
        $VfuncionarioDc = VfuncionarioDc::findByRole($FtAgendamientoActa->dependencia);
        $username = $VfuncionarioDc->getName();

        $assistants = $this->FtActa->getAssistants();

        $names = [];
        foreach ($assistants as $ActDocumentUser) {
            array_push($names, $ActDocumentUser->getUser()->getName());
        }

        $assistantsName = implode('<br>', $names);

        return <<<HTML
        Tienes una invitación para la siguiente reunión.<br><br>
        {$FtAgendamientoActa->subject}<br><br>
        Organizador: {$username}<br><br>
        {$assistantsName}<br><br>
        El día de la reunión podrás participar en la toma de decisiones y opinar haciendo clic en 
        <a class="btn btn-complete" href='{$roomRoute}' >aquí</a><br><br><br>
        Mensaje automático enviado por SAIA-ACTAS
HTML;
    }

    /**
     * ejecuta el envio del correo
     *
     * @param array $destinations
     * @return void
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function send(array $destinations = [])
    {
        if (!$destinations) {
            $destinations = $this->FtActa->getAssistantsEmail();
        }

        $icsRoute = $this->generateIcsFile();
        $body = $this->generateBody();

        $FtAgendamientoActa = $this->FtActa->FtAgendamientoActa;
        $DateTime = new \DateTime($FtAgendamientoActa->date);
        $formated = strftime(
            "%A %d de %B de %Y - %H:%M",
            $DateTime->getTimestamp()
        );
        $subject = "Invitación  {$FtAgendamientoActa->subject} el {$formated}";

        $SendMailController = new SendMailController($subject, $body);
        $SendMailController->setDestinations(
            SendMailController::DESTINATION_TYPE_EMAIL,
            $destinations
        );
        $SendMailController->setAttachments(
            SendMailController::ATTACHMENT_TYPE_ROUTE,
            [$icsRoute]
        );

        $SendMailController->send();
    }

    protected function getRoomRoute(): string
    {
        return sprintf(
            "%s%s%s",
            ABSOLUTE_SAIA_ROUTE,
            "views/modules/actas/dist/qr/index.html?documentId=",
            $this->FtActa->documento_iddocumento
        );
    }

    /**
     * gets the ics description
     *
     * @param $FtAgendamientoActa
     * @return string
     * @date 2020-04-01
     * @author jhon sebastian valencia <sebasjsv97@gmail.com>
     */
    protected function getIcsDescription($FtAgendamientoActa): string
    {
        $roomRoute = $this->getRoomRoute();
        $link = "<br><br>El día de la reunión podrás participar en la toma de decisiones y opinar haciendo clic en 
        <a class=\"btn btn-complete\" href='{$roomRoute}' >aquí</a><br><br><br>";
        return sprintf("%s %s", $FtAgendamientoActa->subject, $link);
    }
}
