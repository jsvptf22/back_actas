<?php

namespace Saia\Actas\models;

use Saia\Core\model\Model;

class ActQuestion extends Model
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
        $this->dbAttributes = (object)[
            'safe' => [
                'label',
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

    protected function beforeUpdate()
    {
        if (!$this->updated_at) {
            $this->updated_at = date('Y-m-d H:i:s');
        }

        return true;
    }

    /**
     * obtiene las opciones activas
     *
     * @return array
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2020-07-26
     */
    public function getOptions()
    {
        return ActQuestionOption::findAllByAttributes([
            'fk_act_question' => $this->getPK(),
            'state' => 1
        ]);
    }
}
