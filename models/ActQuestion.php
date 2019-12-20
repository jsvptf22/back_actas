<?php

namespace Saia\Actas\models;

use Saia\Actas\formatos\acta\FtActa;

class ActQuestion extends \Model
{
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
                'question',
                'state',
                'fk_funcionario',
                'fk_ft_acta',
                'created_at',
                'updated_at',
            ],
            'date' => ['created_at', 'updated_at'],
            'table' => 'act_question',
            'primary' => 'idact_question',
        ];
    }

    /* funcionalidad a ejecutar antes de crear un registro
     *
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-03-19
     */
    protected function beforeCreate()
    {
        if (!$this->created_at) {
            $this->created_at = date('Y-m-d H:i:s');
        }

        if (!$this->state) {
            $this->state = 1;
        }

        return true;
    }

    /* funcionalidad a ejecutar antes de crear un registro
     *
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-03-19
     */
    protected function afterCreate()
    {
        return $this->notifyQuestion();
    }

    /* funcionalidad a ejecutar antes de editar un registro
     *
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-03-19
     */
    protected function beforeUpdate()
    {
        if (!$this->updated_at) {
            $this->updated_at = date('Y-m-d H:i:s');
        }

        return true;
    }

    /**
     * envia correo indicando enlace de la nueva pregunta
     *
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function notifyQuestion()
    {
        $FtActa = new FtActa($this->fk_ft_acta);

        $room = ABSOLUTE_SAIA_ROUTE . "views/modules/actas/views/question/decide.php?q=";
        $room .= \CriptoController::encrypt_blowfish(json_encode([
            'id' => $this->getPK(),
            'question' => $this->question,
        ]));
        $body = "Para opinar a la pregunta {$this->question} haga click <a href='{$room}'>aquí</a>";

        $SendMailController = new \SendMailController('Nueva decicion por opinar', $body);
        $SendMailController->setDestinations(
            \SendMailController::DESTINATION_TYPE_EMAIL,
            $FtActa->getAssistantsEmail()
        );
        $SendMailController->send();
    }
}
