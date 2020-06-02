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
     * @var FtActaService|FtActa
     */
    protected FtActaService $FtActaService;

    /**
     * MeetMailInvitation constructor.
     *
     * @param FtActaService $FtActaService
     */
    public function __construct(FtActaService $FtActaService)
    {
        $this->FtActaService = $FtActaService;
    }

    /**
     * genera el archivo ics de la reunion
     *
     * @return string
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function generateIcsFile()
    {
        global $rootPath;

        $FtActa = $this->FtActaService->getFtActa();
        $duration = $FtActa->duracion ?? '60';

        $DateInterval = new \DateInterval("PT{$duration}M");
        $DateTime = new \DateTime($FtActa->fecha_inicial);
        $DateTime->add($DateInterval);

        $properties = [
            'description' => $this->getIcsDescription(),
            'dtstart' => $FtActa->getDateAttribute('fecha_inicial', 'Y-m-d H:i:s'),
            'dtend' => $DateTime->format('Y-m-d H:i:s'),
            'summary' => $FtActa->asunto,
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
     * @date   2020
     */
    public function generateBody()
    {
        $FtActa = $this->FtActaService->getFtActa();
        $roomRoute = $this->getRoomRoute();
        $assistants = $this->FtActaService->getAssistants();
        $VfuncionarioDc = VfuncionarioDc::findByRole((int)$FtActa->dependencia);

        $names = [];
        foreach ($assistants as $ActDocumentUser) {
            array_push($names, $ActDocumentUser->getUser()->getName());
        }

        $assistantsName = implode('<br>', $names);

        return <<<HTML
        Tienes una invitación para la siguiente reunión.<br><br>
        {$FtActa->asunto}<br><br>
        Organizador: {$VfuncionarioDc->getName()}<br><br>
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
     * @return bool|string
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function send(array $destinations = [])
    {
        if (!$destinations) {
            $destinations = $this->FtActaService->getAssistantsEmail();
        }

        $icsRoute = $this->generateIcsFile();
        $body = $this->generateBody();

        $DateTime = new \DateTime($this->FtActaService->getFtActa()->fecha_inicial);
        $formated = strftime(
            "%A %d de %B de %Y - %H:%M",
            $DateTime->getTimestamp()
        );
        $subject = "Invitación {$this->FtActaService->getFtActa()->asunto} el {$formated}";

        $SendMailController = new SendMailController($subject, $body);
        $SendMailController->setDestinations(
            SendMailController::DESTINATION_TYPE_EMAIL,
            $destinations
        );
        $SendMailController->setAttachments(
            SendMailController::ATTACHMENT_TYPE_ROUTE,
            [$icsRoute]
        );
      
        return $SendMailController->send();
    }

    protected function getRoomRoute(): string
    {
        return sprintf(
            "%s%s%s",
            ABSOLUTE_SAIA_ROUTE,
            "views/modules/actas/dist/qr/index.html?documentId=",
            $this->FtActaService->getFtActa()->documento_iddocumento
        );
    }

    /**
     * gets the ics description
     *
     * @return string
     * @date   2020-04-01
     * @author jhon sebastian valencia <sebasjsv97@gmail.com>
     */
    protected function getIcsDescription(): string
    {
        $roomRoute = $this->getRoomRoute();
        $link = "<br><br>El día de la reunión podrás participar en la toma de decisiones y opinar haciendo clic en 
        <a class=\"btn btn-complete\" href='{$roomRoute}' >aquí</a><br><br><br>";
        return sprintf("%s %s", $this->FtActaService->getFtActa()->asunto, $link);
    }
}
