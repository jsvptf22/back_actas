<?php

namespace Saia\Actas\controllers;

use Saia\Actas\formatos\acta\FtActa;
use Saia\models\vistas\VfuncionarioDc;
use Saia\controllers\SessionController;
use Saia\controllers\GuardarFtController;
use Saia\Actas\controllers\FtActaController;
use Saia\Actas\formatos\agendamiento_acta\FtAgendamientoActa;

class FtAgendamientoActaController
{

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
        $this->createDocument($data);
    }

    public function createDocument($data)
    {
        if (!$data->subject) {
            throw new \Exception('Debe indicar el asunto', 1);
        }

        if (!$data->initialDate) {
            throw new \Exception('Debe indicar la fecha', 1);
        }

        $FtAgendamientoActa = new FtAgendamientoActa();
        $GuardarFtController = new GuardarFtController($FtAgendamientoActa->getFormat());

        $userId = SessionController::getValue('idfuncionario');
        $firstRole = VfuncionarioDc::getFirstUserRole($userId);

        $documentId = $GuardarFtController->create([
            'dependencia' => $firstRole,
            'subject' => $data->subject,
            'date' => $data->initialDate,
            'state' => 1,
        ]);

        if (!$documentId) {
            throw new \Exception("Error al agendar", 1);
        }

        $this->FtAgendamientoActa = FtAgendamientoActa::findByDocumentId($documentId);

        $defaultAssistant = (object) [
            'id' => $firstRole,
            'external' => 0
        ];
        $userList = json_decode($data->users);
        array_push($userList, $defaultAssistant);

        $date = $this->FtAgendamientoActa->getDateAttribute('date', 'Y-m-d H:i:s');
        $documentData = (object) [
            'fk_agendamiento_act' => $this->FtAgendamientoActa->getPK(),
            'initialDate' => $date,
            'subject' => $this->FtAgendamientoActa->subject,
            'userList' => $userList
        ];

        $FtActa = new FtActa();
        $FtActaController = new FtActaController($FtActa);
        $FtActaController->saveDocument($documentData);
        $FtActaController->sendInvitations();
    }
}
