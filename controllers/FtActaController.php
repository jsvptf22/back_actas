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

        $this->refreshItems($data->topicList, $data->topicListDescription);
        $this->refreshUsers($data->userList);
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
    public function refreshItems($topicList, $topicListDescriptions)
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

    public function refreshUsers($userList)
    {
        ActDocumentUser::executeUpdate([
            'state' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'state' => 1,
            'fk_ft_acta' => $this->FtActa->getPK()
        ]);

        foreach ($userList as $user) {
            $ActDocumentUser = ActDocumentUser::findByAttributes([
                'fk_ft_acta' => $this->FtActa->getPK(),
                'identification' => $user->id,
                'external' => $user->external
            ]);

            if (!$ActDocumentUser) {
                $ActDocumentUser = new ActDocumentUser();
            }

            $ActDocumentUser->setAttributes([
                'state' => 1,
                'relation' => ActDocumentUser::RELATION_ASSISTANT,
                'identification' => $user->id,
                'external' => $user->external
            ]);
            $ActDocumentUser->save();
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
            'documentId' => $this->documento_iddocumento,
            'identificator' => $this->Documento->numero,
            'initialDate' => $this->fecha_inicial,
            'finalDate' => $this->fecha_final,
            'subject' => $this->asunto,
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

    public function prepareAssistants()
    {
        $assistants = [];

        foreach ($this->FtActa->getAssistants() as $ActDocumentUser) {
            array_push($assistants, [
                'id' => $ActDocumentUser->getPK(),
                'name' => $ActDocumentUser->getUser()->getName(),
                'text' => $ActDocumentUser->getUser()->getName(),
                'external' => $ActDocumentUser->external,
            ]);
        }

        return $assistants;
    }
}
