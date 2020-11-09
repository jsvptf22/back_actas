<?php


namespace Saia\Actas\controllers;


use DateInterval;
use DateTime;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentUser;
use Saia\controllers\tareas\TareaService;
use Saia\models\tarea\Tarea;

class ScheduleTaskMaker
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
     * crea la tarea para todos
     * los asistentes agendados
     *
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-11-04
     */
    public function createTask()
    {
        $assistants = $this->FtActaService->getAssistants(0);
        $users = [];

        foreach ($assistants as $ActDocumentUser) {
            array_push($users, $ActDocumentUser->getUser()->getPK());
        }

        $TareaService = new TareaService(new Tarea());
        $TareaService->update([
            'tipo' => Tarea::TIPO_SIMPLE,
            'nombre' => $this->getTaskName(),
            'fecha_inicial' => $this->getInitialDate(),
            'fecha_final' => $this->getFinalDate(),
        ]);

        $ActDocumentUser = $this->FtActaService->getRole(ActDocumentUser::RELATION_ORGANIZER);
        $maker = $ActDocumentUser->getUser()->getPK();

        $TareaService->setMaker($maker);
        $TareaService->setManagers($users);
        /*$TareaService->setDocument(
            $this->FtActaService->getFtActa()->documento_iddocumento,
            Tarea::TIPO_RECORDATORIO,
            $maker
        );*/
    }

    /**
     * obtiene el nombre de la tarea
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-11-04
     */
    private function getTaskName(): string
    {
        $subject = $this->FtActaService->getFtActa()->asunto;
        return sprintf("Reunión - (%s)", $subject);
    }

    /**
     * obtiene la fecha inicial de la reunion
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-11-04
     */
    private function getInitialDate()
    {
        return $this->FtActaService->getFtActa()->fecha_inicial;
    }

    /**
     * calcula la fecha final de a reunion
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-11-04
     */
    private function getFinalDate()
    {
        $duration = $this->FtActaService->getFtActa()->duracion ?? FtActa::DEFAULT_DURATION;
        $DateInterval = new DateInterval("PT{$duration}M");

        $DateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->getInitialDate());
        $DateTime->add($DateInterval);

        return $DateTime->format('Y-m-d H:i:s');
    }
}