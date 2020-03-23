<?php

namespace Saia\Actas\controllers;

use Exception;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentTopic;
use Saia\Actas\models\ActDocumentUser;
use Saia\Actas\models\ActQuestion;
use Saia\controllers\documento\QRDocumentoController;
use Saia\controllers\SaveDocument;
use Saia\controllers\SessionController;
use Saia\models\documento\DocumentoTarea;
use Saia\models\tarea\TareaFuncionario;
use Saia\models\vistas\VfuncionarioDc;

class FtActaController
{

    /**
     * almacena la instancia de la ft
     *
     * @var FtActa
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    protected FtActa $FtActa;

    public function __construct(FtActa $FtActa)
    {
        $this->FtActa = $FtActa;
    }

    /**
     * crea o modifica el documento
     *
     * @param array $data
     * @return FtActa
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function saveDocument($data)
    {
        $userId = SessionController::getValue('idfuncionario');
        $attributes = [
            'fk_agendamiento_act' => $data->fk_agendamiento_act,
            'fecha_final' => $data->initialDate,
            'asunto' => $data->subject,
            'dependencia' => VfuncionarioDc::getFirstUserRole($userId),
            'estado' => 1,
            'room' => $this->FtActa->getRoom()
        ];

        if ($this->FtActa->getPK()) {
            $GuardarFtController = new SaveDocument(
                $this->FtActa->getFormat(),
                $attributes
            );
            $GuardarFtController->edit(
                $this->FtActa->documento_iddocumento
            );
            $this->FtActa->refresh();
        } else {
            $attributes['fecha_inicial'] = date('Y-m-d H:i:s');
            $GuardarFtController = new SaveDocument(
                $this->FtActa->getFormat(),
                $attributes
            );
            $documentId = $GuardarFtController->create();
            $this->FtActa = FtActa::findByDocumentId($documentId);
        }

        $this->refreshTopics($data->topicList, $data->topicListDescription);
        $this->refreshAssistants($data->userList, $data->fk_agendamiento_act);
        $this->refreshRoles($data->roles);
        $this->refreshTasks($data->tasks);
        $this->refreshQuestions($data->questions);
        $this->generateQr();

        return $this->FtActa;
    }

    /**
     * refresca los temas del acta
     *
     * @param array $topicList
     * @param array $topicListDescriptions
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-11-26
     */
    public function refreshTopics($topicList, $topicListDescriptions)
    {
        ActDocumentTopic::executeUpdate([
            'state' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'state' => 1,
            'fk_ft_acta' => $this->FtActa->getPK()
        ]);

        foreach ($topicList as $topic) {
            $ActDocumentTopic = ActDocumentTopic::findByAttributes([
                'idact_document_topic' => $topic->id,
                'fk_ft_acta' => $this->FtActa->getPK()
            ]);

            if (!$ActDocumentTopic) {
                $ActDocumentTopic = new ActDocumentTopic();
            }

            $ActDocumentTopic->setAttributes([
                'fk_ft_acta' => $this->FtActa->getPK(),
                'state' => 1,
                'name' => $topic->label,
                'description' => ''
            ]);

            foreach ($topicListDescriptions as $key => $item) {
                if ($topic->id == $item->topic) {
                    $ActDocumentTopic->description = $item->description;
                    unset($topicListDescriptions[$key]);
                    break;
                }
            }

            $ActDocumentTopic->save();
        }
    }

    /**
     * almacena los asistentes de la reunion
     *
     * @param array $userList
     * @param integer $fk_agendamiento_act
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function refreshAssistants($userList, $fk_agendamiento_act = null)
    {
        ActDocumentUser::inactiveUsersByRelation(
            $this->FtActa->getPK(),
            ActDocumentUser::RELATION_ASSISTANT
        );

        foreach ($userList as $user) {
            $user->fk_agendamiento_act = $fk_agendamiento_act;

            ActDocumentUser::updateUserRelation(
                $this->FtActa->getPK(),
                $user,
                ActDocumentUser::RELATION_ASSISTANT
            );
        }
    }

    /**
     * actualiza los roles de secretario y presidente
     *
     * @param object $roles
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function refreshRoles($roles)
    {
        $this->updateRole(
            $roles->secretary ?? null,
            ActDocumentUser::RELATION_SECRETARY
        );

        $this->updateRole(
            $roles->president ?? null,
            ActDocumentUser::RELATION_PRESIDENT
        );
    }

    /**
     * actualiza un rol en el documento
     *
     * @param object $user
     * @param integer $relationType ej. ActDocumentUser::RELATION_*
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function updateRole($user, $relationType)
    {
        ActDocumentUser::inactiveUsersByRelation(
            $this->FtActa->getPK(),
            $relationType
        );

        if ($user) {
            ActDocumentUser::updateUserRelation(
                $this->FtActa->getPK(),
                $user,
                $relationType
            );
        }
    }

    /**
     * actualiza las tareas vinculadas al documento
     *
     * @param array $tasks
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function refreshTasks($tasks)
    {
        $documentId = $this->FtActa->documento_iddocumento;

        DocumentoTarea::inactiveByDocument($documentId);

        foreach ($tasks as $task) {
            $DocumentoTarea = DocumentoTarea::findByAttributes([
                'fk_tarea' => $task->id,
                'fk_documento' => $documentId
            ]);

            if (!$DocumentoTarea) {
                $DocumentoTarea = new DocumentoTarea();
            }

            $DocumentoTarea->setAttributes([
                'fk_tarea' => $task->id,
                'fk_documento' => $documentId,
                'estado' => 1
            ]);
            $DocumentoTarea->save();
        }
    }

    /**
     * actualiza las preguntas del documento
     *
     * @param array $data
     * @return true
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function refreshQuestions($data)
    {
        $userId = SessionController::getValue('idfuncionario');
        ActQuestion::executeDelete([
            'fk_ft_acta' => $this->FtActa->getPK()
        ]);

        foreach ($data->items as $question) {
            ActQuestion::newRecord([
                'fk_ft_acta' => $this->FtActa->getPK(),
                'label' => $question->label,
                'fk_funcionario' => $userId,
                'approve' => $question->approve,
                'reject' => $question->reject
            ]);
        }

        return true;
    }

    /**
     * obtiene la informacion para actualiza el
     * documentbuilder
     *
     * @return object
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function getDocumentBuilderData()
    {
        return (object)[
            'id' => $this->FtActa->getPK(),
            'documentId' => $this->FtActa->documento_iddocumento,
            'identificator' => $this->FtActa->Documento->numero,
            'initialDate' => $this->FtActa->fecha_inicial,
            'finalDate' => $this->FtActa->fecha_final,
            'subject' => $this->FtActa->asunto,
            'topics' => $this->prepareTopics(),
            'userList' => $this->prepareAssistants(),
            'roles' => $this->prepareRoles(),
            'tasks' => $this->prepareTasks(),
            'fk_agendamiento_act' => $this->FtActa->fk_agendamiento_act,
            'questions' => [
                'room' => $this->FtActa->getRoom(),
                'items' => $this->prepareQuestions()
            ],
            'qrUrl' => $this->FtActa->Documento->getQR()
        ];
    }

    /**
     * obtiene la lista de temas del documento
     *
     * @return array
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function prepareTopics()
    {
        $topics = [];

        foreach ($this->FtActa->getTopics() as $ActDocumentTopic) {
            array_push($topics, [
                'id' => $ActDocumentTopic->getPK(),
                'name' => $ActDocumentTopic->name,
                'description' => $ActDocumentTopic->description
            ]);
        }

        return $topics;
    }

    /**
     * obtiene la lista de asistentes del documento
     *
     * @return array
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function prepareAssistants()
    {
        $assistants = [];

        foreach ($this->FtActa->getAssistants() as $ActDocumentUser) {
            array_push($assistants, $ActDocumentUser->prepareData());
        }

        return $assistants;
    }

    /**
     * obtiene las instancias de los usuarios asignados a roles
     *
     * @return object
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function prepareRoles()
    {
        $response = new \stdClass();
        $president = $this->FtActa->getPresident();

        if ($president) {
            $response->president = $president->prepareData();
        }

        $secretary = $this->FtActa->getSecretary();

        if ($secretary) {
            $response->secretary = $secretary->prepareData();
        }

        return $response;
    }

    /**
     * obtiene la lista de tareas del documento
     *
     * @return array
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function prepareTasks()
    {
        $response = [];

        foreach ($this->FtActa->Documento->getTasks() as $Tarea) {
            $managerIdentifications = TareaFuncionario::findColumn(
                'fk_funcionario',
                [
                    'estado' => 1,
                    'fk_tarea' => $Tarea->getPK(),
                    'tipo' => TareaFuncionario::TIPO_RESPONSABLE
                ]
            );

            array_push($response, [
                'id' => $Tarea->getPK(),
                'name' => $Tarea->getName(),
                'managers' => $managerIdentifications
            ]);
        }

        return $response;
    }

    /**
     * obtiene el listado de preguntas
     *
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function prepareQuestions()
    {
        $data = [];
        $questions = $this->FtActa->questions;

        foreach ($questions as $ActQuestion) {
            array_push($data, $ActQuestion->getAttributes());
        }

        return $data;
    }

    /**
     * envia los correos con la invitacion ics
     * a los usuarios indicados en la planeacion
     *
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-10
     */
    public function sendInvitations()
    {
        $ActaMailInvitation = new ActaMailInvitation($this->FtActa);
        $ActaMailInvitation->send();
    }

    /**
     * Genera el codigo qr en caso de no existir
     *
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function generateQr()
    {
        if (!$this->FtActa->Documento->DocumentoVerificacion) {
            $route = sprintf(
                "%s%s%s",
                ABSOLUTE_SAIA_ROUTE,
                "views/modules/actas/dist/qr/index.html?documentId=",
                $this->FtActa->documento_iddocumento
            );

            $QR = new QRDocumentoController(
                $this->FtActa->Documento,
                $route
            );
            $QR->getRouteQR();
        }
    }
}
