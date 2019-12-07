<?php

namespace Saia\Actas\controllers;

use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentTopic;
use Saia\Actas\models\ActDocumentUser;

class FtActaController
{

    /**
     * almacena la instancia de la ft
     *
     * @var FtActa
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    protected $FtActa;

    public function __construct(FtActa $FtActa)
    {
        $this->FtActa = $FtActa;
    }

    /**
     * crea o modifica el documento
     *
     * @param array $data
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function saveDocument($data)
    {
        $userId = \SessionController::getValue('idfuncionario');
        $attributes = [
            'fecha_final' => $data->initialDate,
            'asunto' => $data->subject,
            'dependencia' => \VfuncionarioDc::getFirstUserRole($userId),
            'estado' => 1
        ];

        $Formato = $this->FtActa->getFormat();
        $GuardarFtController = new \GuardarFtController($Formato->getPK());

        if ($this->FtActa->getPK()) {
            $documentId = $GuardarFtController->edit(
                $attributes,
                $this->FtActa->documento_iddocumento
            );
            $this->FtActa->refresh();
        } else {
            $attributes['fecha_inicial'] = date('Y-m-d H:i:s');
            $documentId = $GuardarFtController->create($attributes);
            $this->FtActa = FtActa::findByDocumentId($documentId);
        }

        $this->refreshTopics($data->topicList, $data->topicListDescription);
        $this->refreshAssistants($data->userList);
        $this->refreshRoles($data->roles);
    }

    /**
     * refresca los temas del acta
     *
     * @param array $topicList
     * @param array $topicListDescriptions
     * @return array
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
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
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
     * obtiene la informacion para actualiza el
     * documentbuilder
     *
     * @return object
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function getDocumentBuilderData()
    {
        return (object) [
            'documentId' => $this->FtActa->documento_iddocumento,
            'identificator' => $this->FtActa->Documento->numero,
            'initialDate' => $this->FtActa->fecha_inicial,
            'finalDate' => $this->FtActa->fecha_final,
            'subject' => $this->FtActa->asunto,
            'topics' => $this->prepareTopics(),
            'userList' => $this->prepareAssistants()
        ];
    }

    /**
     * obtiene la lista de temas del documento
     *
     * @return array
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
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function prepareAssistants()
    {
        $assistants = [];

        foreach ($this->FtActa->getAssistants() as $ActDocumentUser) {
            array_push($assistants, [
                'id' => $ActDocumentUser->identification,
                'name' => $ActDocumentUser->getUser()->getName(),
                'text' => $ActDocumentUser->getUser()->getName(),
                'external' => $ActDocumentUser->external,
            ]);
        }

        return $assistants;
    }
}
