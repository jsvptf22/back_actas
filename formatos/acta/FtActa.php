<?php

namespace Saia\Actas\formatos\acta;

use Saia\Actas\models\ActDocumentTopic;
use Saia\Actas\models\ActDocumentUser;

class FtActa extends FtActaProperties
{
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * obtiene los temas activos del documento
     *
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function getTopics()
    {
        return ActDocumentTopic::findAllByAttributes([
            'fk_ft_acta' => $this->getPK(),
            'state' => 1
        ]);
    }

    /**
     * obtiene los asistentes de la reunion
     *
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function getAssistants()
    {
        return ActDocumentUser::findAllByAttributes([
            'fk_ft_acta' => $this->getPK(),
            'state' => 1,
            'relation' => ActDocumentUser::RELATION_ASSISTANT
        ]);
    }
}
