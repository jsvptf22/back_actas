<?php

namespace Saia\Actas\models;

class ActDocumentUser extends \Model
{

    /**
     * identifica una relacion de tipo asistente
     */
    const RELATION_ASSISTANT = 1;

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
                'fk_ft_acta',
                'relation',
                'state',
                'identification',
                'external',
                'created_at',
                'updated_at',
            ],
            'date' => ['created_at', 'updated_at']
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

    public function getUser()
    {
        pendiente desarrollar las relaciones con tercero y funcionario
    }
}
