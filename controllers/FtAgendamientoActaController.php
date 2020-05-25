<?php

namespace Saia\Actas\controllers;

use Exception;
use Saia\Actas\formatos\acta\FtActa;
use Saia\models\vistas\VfuncionarioDc;
use Saia\controllers\SessionController;
use Saia\Actas\controllers\FtActaController;
use Saia\Actas\formatos\agendamiento_acta\FtAgendamientoActa;

class FtAgendamientoActaController
{

    /**
     * almacena los datos con los que se creara 
     * el agendamiento
     *
     * @var array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    protected $params;

    /**
     * almacena la instancia de la ft
     *
     * @var FtAgendamientoActa
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    protected $FtAgendamientoActa;

    public function __construct(object $data)
    {
        $this->params = $data;
        $this->init();
    }

    public function init()
    {
        $this->checkRequired();
        $this->saveSchedule();
        $this->createEmptyDocument();
    }

    /**
     * verifica los datos requeridos
     *
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function checkRequired()
    {
        if (!$this->params->subject) {
            throw new Exception('Debe indicar el asunto', 1);
        }

        if (!$this->params->initialDate) {
            throw new Exception('Debe indicar la fecha', 1);
        }

        if (!$this->params->duration) {
            throw new Exception('Debe indicar la duración de la reunión', 1);
        }
    }

    /**
     * crea el registro en FtAgendamientoActa
     *
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function saveSchedule()
    {
        $userId = SessionController::getValue('idfuncionario');
        $firstRole = VfuncionarioDc::getFirstUserRole($userId);

        $this->FtAgendamientoActa = new FtAgendamientoActa();
        $this->FtAgendamientoActa->setAttributes([
            'documento_iddocumento' => 0,
            'dependencia' => $firstRole,
            'subject' => $this->params->subject,
            'date' => $this->params->initialDate,
            'state' => 1,
            'duration' => $this->params->duration
        ]);

        $this->FtAgendamientoActa->save();
    }

    /**
     * crea el acta con los valores del agendamiento
     *
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function createEmptyDocument()
    {
        $documentData = $this->getDocumentInitialData();

        $FtActa = new FtActa();
        $FtActaController = new FtActaController($FtActa);
        $FtActaController->saveDocument($documentData);
        $FtActaController->sendInvitations();
    }

    /**
     * genera los datos iniciales para el acta
     *
     * @return object
     * @throws \Doctrine\DBAL\DBALException
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function getDocumentInitialData()
    {
        $date = $this->FtAgendamientoActa->getDateAttribute('date', 'Y-m-d H:i:s');
        return (object) [
            'fk_agendamiento_act' => $this->FtAgendamientoActa->getPK(),
            'initialDate' => $date,
            'subject' => $this->FtAgendamientoActa->subject,
            'userList' => $this->getAssistants()
        ];
    }

    /**
     * genera los asistentes del encuentro
     *
     * @return array
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function getAssistants()
    {
        $userId = SessionController::getValue('idfuncionario');
        $firstRole = VfuncionarioDc::getFirstUserRole($userId);
        $defaultAssistant = (object) [
            'id' => $firstRole,
            'external' => 0
        ];
        $userList = json_decode($this->params->users);
        array_push($userList, $defaultAssistant);

        return $userList;
    }
}
