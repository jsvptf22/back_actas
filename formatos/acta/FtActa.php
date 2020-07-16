<?php

namespace Saia\Actas\formatos\acta;

use Doctrine\DBAL\DBALException;
use Exception;
use Saia\Actas\controllers\FtActaService;
use Saia\Actas\models\ActDocumentUser;
use Saia\Actas\models\ActQuestion;
use Saia\controllers\documento\RouteMaker;
use Saia\controllers\pdf\DocumentPdfGenerator;
use Saia\controllers\SendMailController;
use Saia\models\ruta\Ruta;
use Saia\models\vistas\VfuncionarioDc;

class FtActa extends FtActaProperties
{
    /**
     * FtActa constructor.
     *
     * @param null $id
     * @throws Exception
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * define atributos adicionales sobre el modelo
     *
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    protected function defineMoreAttributes()
    {
        return [
            'relations' => [
                'questions' => [
                    'model' => ActQuestion::class,
                    'attribute' => 'fk_ft_acta',
                    'primary' => 'idft_acta',
                    'relation' => self::BELONGS_TO_MANY
                ]
            ]
        ];
    }

    /**
     * obtiene la instancia de FtActaService
     *
     * @return FtActaService
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-06-01
     */
    public function getFtActaService(): FtActaService
    {
        return new FtActaService($this);
    }

    /**
     * accion a ejecutar despues de radicar
     *
     * @return bool
     * @throws DBALException
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019
     */
    public function afterRad()
    {
        $DocumentPdfGenerator = new DocumentPdfGenerator($this->Documento);
        $route = $DocumentPdfGenerator->refreshFile();

        $SendMailController = new SendMailController(
            'Acta sobre ' . $this->asunto,
            'Se adjunta documento generado en la reunión'
        );

        $SendMailController->setDestinations(
            SendMailController::DESTINATION_TYPE_EMAIL,
            $this->getFtActaService()->getAssistantsEmail()
        );

        $SendMailController->setAttachments(
            SendMailController::ATTACHMENT_TYPE_JSON,
            [$route]
        );

        $SendMailController->send();
        return true;
    }

    /**
     * accion a ejecutar despues de editar
     *
     * @return bool
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019
     */
    public function afterEdit()
    {
        $secretary = $this
            ->getFtActaService()
            ->getRole(ActDocumentUser::RELATION_SECRETARY);
        $president = $this
            ->getFtActaService()
            ->getRole(ActDocumentUser::RELATION_PRESIDENT);

        if (!$secretary || !$president) {
            return true;
        }

        $VfuncionarioDc = VfuncionarioDc::findByAttributes([
            'iddependencia_cargo' => $this->dependencia
        ]);
        $RouteMaker = new RouteMaker(
            $this->Documento,
            $VfuncionarioDc
        );
        $RouteMaker->createRadicationRoute([
            [
                'funCod' => $secretary->getUser()->funcionario_codigo,
                'action' => Ruta::FIRMA_VISIBLE,
                'limitdate' => date('Y-m-d H:i:s')
            ],
            [
                'funCod' => $president->getUser()->funcionario_codigo,
                'action' => Ruta::FIRMA_VISIBLE,
                'limitdate' => date('Y-m-d H:i:s')
            ]
        ]);

        return true;
    }

    /**
     * funciones para el mostrar
     */

    /**
     * lista los nombres de los asistentes internos
     *
     * @return string
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function listInternalAssistants()
    {
        $assistants = $this
            ->getFtActaService()
            ->getAssistants(ActDocumentUser::INTERNAL);

        $names = [];
        foreach ($assistants as $key => $ActDocumentUser) {
            array_push($names, $ActDocumentUser->getUser()->getName());
        }

        return implode(', ', $names);
    }

    /**
     * lista los nombres de los asistentes externos
     *
     * @return string
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function listExternalAssistants()
    {
        $assistants = $this
            ->getFtActaService()
            ->getAssistants(ActDocumentUser::EXTERNAL);

        $names = [];
        foreach ($assistants as $ActDocumentUser) {
            array_push($names, $ActDocumentUser->getUser()->getName());
        }

        return implode(', ', $names);
    }

    /**
     * lista los nombres de los temas tratados
     *
     * @return string
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function listTopics()
    {
        $names = [];
        foreach ($this->getFtActaService()->getTopics() as $ActDocumentTopic) {
            array_push($names, $ActDocumentTopic->name);
        }

        return implode('<br>', $names);
    }


    /**
     * lista los detalles de los temas tratados
     *
     * @return string
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function listTopicDescriptions()
    {
        $response = "";
        foreach ($this->getFtActaService()->getTopics() as $ActDocumentTopic) {
            $response .= $ActDocumentTopic->name . "<br>";
            $response .= $ActDocumentTopic->description . "<br><br>";
        }

        return $response;
    }

    /**
     * obtiene la imagen del codigo qr
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-11-27
     */
    public function qrCodeHtml()
    {
        $route = $this->Documento->getQr();
        return "<img src='{$route}' width='80px' height='80px' alt=''>";
    }

    /**
     * lista las tareas y los responsables asignados
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function listTasks()
    {
        $DocumentoService = $this->Documento->getService();

        $response = "";
        foreach ($DocumentoService->getTasks() as $Tarea) {
            $managers = $Tarea->getService()->getManagers();

            $names = [];
            foreach ($managers as $Funcionario) {
                array_push($names, $Funcionario->getName());
            }

            $response .= sprintf("%s - %s<br>", $Tarea->getName(), implode(', ', $names));
        }

        return $response;
    }

    /**
     * lista las preguntas del acta
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020
     */
    public function listQuestions()
    {
        $response = "";

        foreach ($this->questions as $key => $ActQuestion) {
            $approve = $ActQuestion->approve > $ActQuestion->reject ? 'Aprobado' : 'Rechazado';
            $response .= sprintf("%s - %s<br>", $ActQuestion->label, $approve);
        }

        return $response;
    }
}
