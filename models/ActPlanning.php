<?php

namespace Saia\Actas\models;

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
            'primary' => 'idact_planning'
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
}
