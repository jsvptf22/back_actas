<?php

namespace Saia\Actas\formatos\acta;

use Saia\Actas\models\ActDocumentTopic;
use Saia\Actas\models\ActDocumentUser;
use Saia\Actas\models\ActPlanning;

class FtActa extends FtActaProperties
{

    /**
     * almacena las instancias de ActdocumentTopic
     *
     * @var array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected $topics;

    /**
     * almacena las instancias de ActdocumentUser
     * de tipo ActDocumentUser::RELATION_ASSISTANT
     *
     * @var array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected $assistants;

    /**
     * almacena las instancia de ActdocumentUser
     * de tipo ActDocumentUser::RELATION_PRESIDENT
     *
     * @var array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected $ActDocumentUserPresident;

    /**
     * almacena las instancia de ActdocumentUser
     * de tipo ActDocumentUser::RELATION_PRESIDENT
     *
     * @var array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected $ActDocumentUserSecretary;

    /**
     * almacena la instancia de ActPlanning
     *
     * @var ActPlanning
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-09
     */
    protected $ActPlanning;


    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * obtiene los temas activos del documento
     *
     * @return ActDocumentTopic[]
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function getTopics()
    {
        if (!$this->topics) {
            $this->topics = ActDocumentTopic::findAllByAttributes([
                'fk_ft_acta' => $this->getPK(),
                'state' => 1
            ]);
        }

        return $this->topics;
    }

    /**
     * obtiene los asistentes de la reunion
     *
     * @param boolean $fromPlanning buscar desde la planeacion
     * @return ActDocumentUser[]
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function getAssistants($fromPlanning = false)
    {
        if (!$this->assistants) {
            if ($fromPlanning) {
                $this->assistants = ActDocumentUser::findAllByAttributes([
                    'fk_act_planning' => $this->getPlanning()->getPK(),
                    'state' => 1,
                    'relation' => ActDocumentUser::RELATION_ASSISTANT
                ]);
            } else {
                $this->assistants = ActDocumentUser::findAllByAttributes([
                    'fk_ft_acta' => $this->getPK(),
                    'state' => 1,
                    'relation' => ActDocumentUser::RELATION_ASSISTANT
                ]);
            }
        }

        return $this->assistants;
    }

    /**
     * obtiene la instancia de ActDocumentUserPresident
     *
     * @return ActDocumentUser|null
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function getPresident()
    {
        if (!$this->ActDocumentUserPresident) {
            $this->ActDocumentUserPresident = ActDocumentUser::findByAttributes([
                'fk_ft_acta' => $this->getPK(),
                'state' => 1,
                'relation' => ActDocumentUser::RELATION_PRESIDENT
            ]);
        }

        return $this->ActDocumentUserPresident;
    }

    /**
     * obtiene la instancia de ActDocumentUserSecretary
     *
     * @return ActDocumentUser|null
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function getSecretary()
    {
        if (!$this->ActDocumentUserSecretary) {
            $this->ActDocumentUserSecretary = ActDocumentUser::findByAttributes([
                'fk_ft_acta' => $this->getPK(),
                'state' => 1,
                'relation' => ActDocumentUser::RELATION_SECRETARY
            ]);
        }

        return $this->ActDocumentUserSecretary;
    }

    /**
     * obtiene la instancia de ActPlanning
     *
     * @return ActPlanning
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-09
     */
    public function getPlanning()
    {
        return $this->ActPlanning;
    }

    /**
     * define la instancia de ActPlanning
     *
     * @return ActPlanning
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-09
     */
    public function setPlanning(ActPlanning $ActPlanning)
    {
        return $this->ActPlanning = $ActPlanning;
    }

    /**
     * funciones para el mostrar
     */


    /**
     * lista los nombres de los asistentes internos
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function listInternalAssistants()
    {
        $assistants = $this->getAssistants();

        $internals = array_filter($assistants, function ($ActDocumentUser) {
            return !(int) $ActDocumentUser->external;
        });

        $names = [];
        foreach ($internals as $key => $ActDocumentUser) {
            array_push($names, $ActDocumentUser->getUser()->getName());
        }

        return implode(', ', $names);
    }

    /**
     * lista los nombres de los asistentes externos
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function listExternalAssistants()
    {
        $assistants = $this->getAssistants();

        $externals = array_filter($assistants, function ($ActDocumentUser) {
            return (int) $ActDocumentUser->external;
        });

        $names = [];
        foreach ($externals as $key => $ActDocumentUser) {
            array_push($names, $ActDocumentUser->getUser()->getName());
        }

        return implode(', ', $names);
    }

    /**
     * lista los nombres de los temas tratados
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function listTopics()
    {
        $topics = $this->getTopics();

        $names = [];
        foreach ($topics as $ActDocumentTopic) {
            array_push($names, $ActDocumentTopic->name);
        }

        return implode('<br>', $names);
    }

    /**
     * lista los detalles de los temas tratados
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function listTopicDescriptions()
    {
        $topics = $this->getTopics();

        $response = "";
        foreach ($topics as $ActDocumentTopic) {
            $response .= $ActDocumentTopic->name . "<br>";
            $response .= $ActDocumentTopic->description . "<br><br>";
        }

        return $response;
    }

    /**
     * lista las tareas y los responsables asignados
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function listTasks()
    {
        $tasks = $this->Documento->getTasks();

        $response = "";
        foreach ($tasks as $Tarea) {
            $managers = $Tarea->getManagers();

            $names = [];
            foreach ($managers as $Funcionario) {
                array_push($names, $Funcionario->getName());
            }

            $response .= $Tarea->getName() . " - " . implode(', ', $names);
        }

        return $response;
    }

    /**
     * obtiene el nombre del usuario con rol secretario
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function showSecretary()
    {
        $secretary = $this->getSecretary();

        return $secretary ? $secretary->getUser()->getName() : "";
    }

    /**
     * obtiene el nombre del usuario con rol secretario
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function showPresident()
    {
        $president = $this->getPresident();

        return $president ? $president->getUser()->getName() : "";
    }
}
