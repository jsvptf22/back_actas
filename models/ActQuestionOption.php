<?php

namespace Saia\Actas\models;

use Saia\Core\model\Model;

class ActQuestionOption extends Model
{
    /**
     * define values for dbAttributes
     */
    protected function defineAttributes()
    {
        $this->dbAttributes = (object)[
            'safe' => [
                'label',
                'state',
                'votes',
                'fk_act_question',
                'created_at',
                'updated_at',
            ],
            'date' => ['created_at', 'updated_at'],
            'table' => 'act_question_option',
            'primary' => 'idact_question_option',
        ];
    }

    /**
     * funcionalidad a ejecutar antes de crear un registro
     *
     * @return bool
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-07-26
     */
    protected function beforeCreate()
    {
        $this->created_at ??= date('Y-m-d H:i:s');
        $this->state ??= 1;
        $this->votes ??= 0;

        return true;
    }

    /**
     * funcionalidad a ejecutar antes de actualizar un registro
     *
     * @return bool
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-07-26
     */
    protected function beforeUpdate()
    {
        if (!$this->updated_at) {
            $this->updated_at = date('Y-m-d H:i:s');
        }

        return true;
    }
}
