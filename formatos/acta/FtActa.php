<?php

namespace Saia\Actas\formatos\acta;

use Exception;
use Saia\models\ruta\Ruta;
use Saia\core\DataBaseConnection;
use Saia\Actas\models\ActQuestion;
use Saia\models\ruta\RutaDocumento;
use Saia\Actas\models\ActDocumentUser;
use Saia\Actas\models\ActDocumentTopic;
use Saia\controllers\documento\RutaDocumentoController;
use Saia\Actas\formatos\agendamiento_acta\FtAgendamientoActa;

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
    protected array $assistants;

    /**
     * almacena las instancia de ActdocumentUser
     * de tipo ActDocumentUser::RELATION_PRESIDENT
     *
     * @var ActDocumentUser
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected ActDocumentUser $ActDocumentUserPresident;

    /**
     * almacena las instancia de ActdocumentUser
     * de tipo ActDocumentUser::RELATION_PRESIDENT
     *
     * @var ActDocumentUser
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected ActDocumentUser $ActDocumentUserSecretary;

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * define atributos adicionales sobre el modelo
     *
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    protected function defineMoreAttributes()
    {
        return [
            'relations' => [
                'FtAgendamientoActa' => [
                    'model' => FtAgendamientoActa::class,
                    'attribute' => FtAgendamientoActa::getPrimaryLabel(),
                    'primary' => 'fk_agendamiento_act',
                    'relation' => self::BELONGS_TO_ONE
                ],
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
     * accion a ejecutar despues de editar
     *
     * @return bool
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function afterEdit()
    {
        $secretary = $this->getSecretary();
        $president = $this->getPresident();

        if (!$secretary || !$president) {
            return true;
        }

        $data = [
            'documentId' => $this->documento_iddocumento,
            'type' => RutaDocumento::TIPO_RADICACION,
            'flow' => RutaDocumento::FLUJO_SERIE,
            'data' => [
                [
                    'funCod' => $secretary->getUser()->funcionario_codigo,
                    'action' => Ruta::FIRMA_VISIBLE,
                ],
                [
                    'funCod' => $president->getUser()->funcionario_codigo,
                    'action' => Ruta::FIRMA_VISIBLE,
                ]
            ]
        ];

        $infoRuta = RutaDocumentoController::generateRoute($data);

        if (!$infoRuta->success) {
            throw new Exception("No fue posible generar la ruta de aprobación", 1);
        }

        return true;
    }

    /**
     * obtiene o genera la sala de la reunion
     *
     * @return string
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
     */
    public function getRoom()
    {
        if (!$this->room) {
            $roomName = time();
            $endPoint = "https://asker-jsv.herokuapp.com/api/room/{$roomName}";
            $Client = new \GuzzleHttp\Client();
            $clientRequest = $Client->request('POST', $endPoint, [
                'form_params' => [
                    'hidden' => 1
                ]
            ]);
            $data = json_decode($clientRequest->getBody());

            if (!$data->success) {
                throw new \Exception("Error al generar la sala", 1);
            }

            $this->room = $data->data->roomId;
        }

        return $this->room;
    }

    /**
     * obtiene la lista de correos de los asistentes
     *
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-17
     */
    public function getAssistantsEmail()
    {
        $emails = [];
        $users = DataBaseConnection::getQueryBuilder()
            ->select('a.email')
            ->from('funcionario', 'a')
            ->join('a', 'act_document_user', 'b', 'a.idfuncionario = b.identification')
            ->where('b.external = 0')
            ->andWhere('b.fk_ft_acta = :ft')
            ->setParameter('ft', $this->getPK())
            ->execute()->fetchAll();

        $externals = DataBaseConnection::getQueryBuilder()
            ->select('a.correo')
            ->from('tercero', 'a')
            ->join('a', 'act_document_user', 'b', 'a.idtercero = b.identification')
            ->where('b.external = 1')
            ->andWhere('b.fk_ft_acta = :ft')
            ->setParameter('ft', $this->getPK())
            ->execute()->fetchAll();

        foreach ($users as $row) {
            array_push($emails, $row['email']);
        }

        foreach ($externals as $row) {
            array_push($emails, $row['correo']);
        }

        return $emails;
    }

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
     * obtiene los asistentes de la reunion
     *
     * @return ActDocumentUser[]
     * @throws Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-06
     */
    public function getAssistants()
    {
        if (!$this->assistants) {
            $this->assistants = ActDocumentUser::findAllByAttributes([
                'fk_ft_acta' => $this->getPK(),
                'state' => 1,
                'relation' => ActDocumentUser::RELATION_ASSISTANT
            ]);
        }

        return $this->assistants;
    }

    /**
     * lista los nombres de los asistentes externos
     *
     * @return string
     * @throws Exception
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
        foreach ($externals as $ActDocumentUser) {
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
     * obtiene los temas activos del documento
     *
     * @return ActDocumentTopic[]
     * @throws Exception
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
     * funciones para el mostrar
     */

    /**
     * obtiene la imagen del codigo qr
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-11-27
     */
    public function qrCodeHtml()
    {
        $route = $this->Documento->getQr();
        return "<img src='{$route}' width='80px' height='80px' alt=''>";
    }

    /**
     * lista los detalles de los temas tratados
     *
     * @return string
     * @throws Exception
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

            $response .= sprintf("%s - %s<br>", $Tarea->getName(), implode(', ', $names));
        }

        return $response;
    }

    /**
     * lista las preguntas del acta
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2020
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
     * obtiene la instancia de ActDocumentUserSecretary
     *
     * @return ActDocumentUser|null
     * @throws Exception
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

    /**
     * obtiene la instancia de ActDocumentUserPresident
     *
     * @return ActDocumentUser|null
     * @throws Exception
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
}
