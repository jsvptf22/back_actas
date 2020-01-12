<?php

namespace Saia\Actas\models;

use Saia\Core\model\Model;

class ActQuestionVote extends Model
{
    const ACTION_REJECT = 0;
    const ACTION_APPROVE = 1;

    function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * define values for dbAttributes
     */
    protected function defineAttributes()
    {
        $this->dbAttributes = (object)[
            'safe' => [
                'fk_funcionario',
                'fk_act_question',
                'action',
                'created_at',
                'updated_at',
            ],
            'date' => ['created_at', 'updated_at'],
            'table' => 'act_question_vote',
            'primary' => 'idact_question_vote',
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
}
