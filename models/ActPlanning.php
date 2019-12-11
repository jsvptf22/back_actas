<?php

namespace Saia\Actas\models;

use DateInterval;
use DateTime;

class ActPlanning extends \Model
{

    /**
     * almacena las instancias de ActDocumentUser relacionadas
     *
     * @var array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-10
     */
    protected $users;

    function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * define values for dbAttributes
     */
    protected function defineAttributes()
    {
        $this->dbAttributes = (object) [
            'safe' => [
                'date',
                'subject',
                'state',
                'created_at',
                'updated_at',
            ],
            'date' => ['created_at', 'updated_at', 'date'],
            'table' => 'act_planning',
            'primary' => 'idact_planning',
        ];
    }

    /* funcionalidad a ejecutar antes de crear un registro
     *
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    protected function beforeCreate()
    {
        if (!$this->created_at) {
            $this->created_at = date('Y-m-d H:i:s');
        }

        return true;
    }

    /* funcionalidad a ejecutar antes de editar un registro
     *
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    protected function beforeUpdate()
    {
        if (!$this->updated_at) {
            $this->updated_at = date('Y-m-d H:i:s');
        }

        return true;
    }

    /**
     * obtiene las instancias de ActDocumentUser binculadas
     *
     * @return ActDocumentUser[]
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function getUserRelations()
    {
        if (!$this->users) {
            $this->users = ActDocumentUser::findAllByAttributes([
                'fk_act_planning' => $this->getPK(),
                'state' => 1,
                'relation' => ActDocumentUser::RELATION_ASSISTANT
            ]);
        }

        return $this->users;
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
        global $rootPath;

        $relations = $this->getUserRelations();

        if (!$relations) {
            return;
        }

        $emails = [];
        foreach ($relations as $ActDocumentUser) {
            array_push($emails, $ActDocumentUser->getUserEmail());
        }

        $DateInterval = new DateInterval('P1D');
        $DateTime = new DateTime($this->date);
        $DateTime->add($DateInterval);

        $properties = [
            'description' => $this->subject,
            'dtstart' => $this->date,
            'dtend' => $DateTime->format('Y-m-d H:i:s'),
            'summary' => $this->subject,
            'organizer' => \SessionController::getValue('email')
        ];

        $ics = new \IcsController($properties);
        $content = $ics->to_string();

        $icsRoute = $rootPath . \SessionController::getTemporalDir() . '/invitacion.ics';

        if (!file_put_contents($icsRoute, $content)) {
            throw new \Exception("Error al generar la invitacion", 1);
        }

        $SendMailController = new \SendMailController('Invitación a reunión', ' ');
        $SendMailController->setDestinations(
            \SendMailController::DESTINATION_TYPE_EMAIL,
            $emails
        );
        $SendMailController->setAttachments(
            \SendMailController::ATTACHMENT_TYPE_ROUTE,
            [$icsRoute]
        );
        $SendMailController->send();
    }
}
