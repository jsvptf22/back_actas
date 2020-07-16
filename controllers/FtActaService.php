<?php

namespace Saia\Actas\controllers;

use Exception;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentTopic;
use Saia\Actas\models\ActDocumentUser;
use Saia\Actas\models\ActQuestion;
use Saia\controllers\documento\QRDocumentoController;
use Saia\controllers\functions\CoreFunctions;
use Saia\controllers\functions\Header;
use Saia\controllers\SaveDocument;
use Saia\models\formatos\Formato;
use Saia\models\vistas\VfuncionarioDc;

class FtActaService
{
    /**
     * @var FtActa
     */
    protected FtActa $FtActa;

    /**
     * FtActaService constructor.
     *
     * @param FtActa $FtActa
     */
    public function __construct(FtActa $FtActa)
    {
        $this->FtActa = $FtActa;
    }

    /**
     * obtiene la instancia FtActa
     *
     * @return FtActa
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-06-01
     */
    public function getFtActa(): FtActa
    {
        return $this->FtActa;
    }

    /**
     * crea o modifica el documento
     *
     * @param object         $data
     * @param VfuncionarioDc $VfuncionarioDc
     * @return FtActa
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-06
     */
    public function saveDocument(object $data, VfuncionarioDc $VfuncionarioDc)
    {
        $attributes = [
            'fecha_final' => $data->initialDate,
            'asunto' => $data->subject,
            'dependencia' => $VfuncionarioDc->iddependencia_cargo,
            'estado' => 1,
        ];

        $SaveDocument = new SaveDocument(
            $this->FtActa->getFormat(),
            $VfuncionarioDc
        );

        if ($this->FtActa->getPK()) {
            $SaveDocument->edit(
                $this->FtActa->Documento,
                $attributes
            );
        } else {
            $attributes['fecha_inicial'] = date('Y-m-d H:i:s');
            $SaveDocument->create($attributes);
        }

        $this->FtActa = $SaveDocument->getDocument()->getFt();
        $this->refreshTopics($data->topics);
        $this->refreshAssistants($data->userList);
        $this->refreshRoles($data->roles);
        $this->refreshQuestions($data->questions, $VfuncionarioDc->getPK());
        $this->generateQr();

        return $this->FtActa;
    }

    /**
     * refresca los temas del acta
     *
     * @param array $topics
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-11-26
     */
    public function refreshTopics(array $topics = null)
    {
        ActDocumentTopic::executeUpdate([
            'state' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'state' => 1,
            'fk_ft_acta' => $this->FtActa->getPK()
        ]);

        foreach ($topics as $topic) {
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
                'description' => $topic->description ?? ''
            ]);

            $ActDocumentTopic->save();
        }
    }

    /**
     * almacena los asistentes de la reunion
     *
     * @param array $userList
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-06
     */
    public function refreshAssistants($userList)
    {
        ActDocumentUser::inactiveUsersByRelation(
            $this->FtActa->getPK(),
            ActDocumentUser::RELATION_ASSISTANT
        );

        foreach ($userList as $user) {
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
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
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

        $this->updateRole(
            $roles->organizer ?? null,
            ActDocumentUser::RELATION_ORGANIZER
        );
    }

    /**
     * actualiza un rol en el documento
     *
     * @param object  $user
     * @param integer $relationType ej. ActDocumentUser::RELATION_*
     * @return void
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019
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
     * actualiza las preguntas del documento
     *
     * @param array $questions
     * @param int   $userId
     * @return true
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function refreshQuestions(array $questions = null, int $userId = null)
    {
        foreach ($questions as $question) {
            $ActQuestion = new ActQuestion($question->idact_question);
            $ActQuestion->setAttributes([
                'fk_ft_acta' => $this->FtActa->getPK(),
                'label' => $question->label,
                'fk_funcionario' => $userId,
                'approve' => $question->approve,
                'reject' => $question->reject
            ]);

            $ActQuestion->save();
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
     * @date   2019-12-06
     */
    public function getDocumentBuilderData()
    {
        $userList = PublicUserPreparer::getFromCollection($this->getAssistants());

        return (object)[
            'id' => $this->FtActa->getPK(),
            'documentId' => $this->FtActa->documento_iddocumento,
            'identificator' => $this->FtActa->Documento->numero,
            'initialDate' => $this->FtActa->fecha_inicial,
            'finalDate' => $this->FtActa->fecha_final,
            'subject' => $this->FtActa->asunto,
            'topics' => $this->prepareTopics(),
            'userList' => $userList,
            'roles' => $this->prepareRoles(),
            'tasks' => $this->prepareTasks(),
            'questions' => $this->prepareQuestions(),
            'qrUrl' => $this->FtActa->Documento->getQR(),
            'headers' => $this->getFormatHeaders()
        ];
    }

    /**
     * obtiene la lista de temas del documento
     *
     * @return array
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-06
     */
    public function prepareTopics()
    {
        $topics = [];

        foreach ($this->getTopics() as $ActDocumentTopic) {
            array_push($topics, [
                'id' => $ActDocumentTopic->getPK(),
                'label' => $ActDocumentTopic->name,
                'description' => $ActDocumentTopic->description
            ]);
        }

        return $topics;
    }

    /**
     * obtiene las instancias de los usuarios asignados a roles
     *
     * @return object
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function prepareRoles()
    {
        $response = new \stdClass();

        if ($president = $this->getRole(ActDocumentUser::RELATION_PRESIDENT)) {
            $response->president = $president->prepareData();
        }

        if ($secretary = $this->getRole(ActDocumentUser::RELATION_SECRETARY)) {
            $response->secretary = $secretary->prepareData();
        }

        if ($organizer = $this->getRole(ActDocumentUser::RELATION_ORGANIZER)) {
            $response->organizer = $organizer->prepareData();
        }

        return $response;
    }

    /**
     * obtiene la lista de tareas del documento
     *
     * @return array
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function prepareTasks()
    {
        $response = [];

        $DocumentoService = $this->FtActa->Documento->getService();
        foreach ($DocumentoService->getTasks() as $Tarea) {
            $TareaService = $Tarea->getService();

            $managers = [];
            foreach ($TareaService->getManagers() as $Funcionario) {
                array_push($managers, $Funcionario->getName());
            }

            array_push($response, [
                'id' => $Tarea->getPK(),
                'name' => $Tarea->getName(),
                'managers' => implode(", ", $managers),
                'limitDate' => $Tarea->getDateAttribute('fecha_final')
            ]);
        }

        return $response;
    }

    /**
     * obtiene el listado de preguntas
     *
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
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
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-10
     */
    public function sendInvitations()
    {
        $ActaMailInvitation = new MeetMailInvitation($this);
        $ActaMailInvitation->send();
    }

    /**
     * Genera el codigo qr en caso de no existir
     *
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
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

    /**
     * obtiene los temas activos del documento
     *
     * @return ActDocumentTopic[]
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-06
     */
    public function getTopics()
    {
        return ActDocumentTopic::findAllByAttributes([
            'fk_ft_acta' => $this->FtActa->getPK(),
            'state' => 1
        ]);
    }

    /**
     * obtiene los asistentes de la reunion
     *
     * @param int|null $external
     * @return ActDocumentUser[]
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-06
     */
    public function getAssistants(int $external = null)
    {
        $filter = [
            'fk_ft_acta' => $this->FtActa->getPK(),
            'state' => 1,
            'relation' => ActDocumentUser::RELATION_ASSISTANT,
        ];

        if (!is_null($external)) {
            $filter['external'] = $external;
        }

        return ActDocumentUser::findAllByAttributes($filter);
    }

    /**
     * obtiene la lista de correos de los asistentes
     *
     * @return array
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-17
     */
    public function getAssistantsEmail()
    {
        $emails = [];
        foreach ($this->getAssistants() as $ActDocumentUser) {
            array_push($emails, $ActDocumentUser->getUserEmail());
        }

        return $emails;
    }

    /**
     * obtiene un la instancia ActDocumentUser
     * de un rol indicado
     *
     * @param int $role
     * @return ActDocumentUser|null
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019
     */
    public function getRole(int $role)
    {
        return ActDocumentUser::findByAttributes([
            'fk_ft_acta' => $this->FtActa->getPK(),
            'state' => 1,
            'relation' => $role
        ]);
    }

    public function getFormatHeaders()
    {
        $Formato = $this->FtActa->getFormat();
        $headerBase = $Formato->getHeader()->contenido;
        $footerBase = $Formato->getFooter()->contenido;

        return [
            'header' => self::replaceHeaderFunctions($headerBase),
            'footer' => self::replaceHeaderFunctions($footerBase)
        ];
    }

    private static function replaceHeaderFunctions($baseContent)
    {
        $Formato = Formato::findByAttributes(['nombre' => 'acta']);
        $functions = Header::getFunctionsFromString($baseContent);
        $functions = str_replace(['{*', '*}'], '', $functions);
        $values = CoreFunctions::getVariableValue($functions);
        $values['nombre_formato'] = CoreFunctions::nombre_formato(null, $Formato->getPK());

        foreach ($values as $key => $value) {
            $baseContent = str_replace(
                "{*{$key}*}",
                $value,
                $baseContent
            );
        }

        return $baseContent;
    }
}
