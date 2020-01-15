<?php

namespace Saia\Actas\models;

use Saia\models\Funcionario;
use Saia\Actas\controllers\PreparedPublicUserData;
use Saia\Core\model\Model;
use Saia\models\Tercero;

class ActDocumentUser extends Model
{
    /**
     * identifica una relacion de tipo asistente
     */
    const RELATION_ASSISTANT = 1;

    /**
     * identifica una relacion de tipo presidente
     */
    const RELATION_PRESIDENT = 2;

    /**
     * identifica una relacion de tipo secretario
     */
    const RELATION_SECRETARY = 3;

    /**
     * almacena la clase que prepara los datos para el cliente
     *
     * @var PreparedPublicUserData
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected $PreparedPublicUserData;


    function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * inactiva todas las relaciones de un documento
     * basadas en un tipo de relacion
     *
     * @param integer $fk_ft_acta
     * @param integer $relationType
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public static function inactiveUsersByRelation($fk_ft_acta, $relationType)
    {
        return ActDocumentUser::executeUpdate([
            'state' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ], [
            'state' => 1,
            'relation' => $relationType,
            'fk_ft_acta' => $fk_ft_acta
        ]);
    }

    /* funcionalidad a ejecutar antes de crear un registro
     *
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */

    /**
     * crea o actualiza la relacion de un usuario con el documento
     *
     * @param integer $fk_ft_acta
     * @param object $user
     * @param integer $relationType
     * @return void
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public static function updateUserRelation($fk_ft_acta, $user, $relationType)
    {
        ActDocumentUser::newRecord([
            'fk_ft_acta' => $fk_ft_acta,
            'state' => 1,
            'relation' => $relationType,
            'identification' => $user->id,
            'fk_act_planning' => $user->planning,
            'external' => $user->external ?? 1
        ]);
    }

    /* funcionalidad a ejecutar antes de editar un registro
     *
     * @return boolean
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */

    /**
     * obtiene el correo del usuario
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-10
     */
    public function getUserEmail()
    {
        return $this->getUser() instanceof Funcionario ?
            $this->getUser()->email : $this->getUser()->correo;
    }

    /**
     * obtiene la instancia del usuario
     *
     * @return Funcionario|Tercero
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function getUser()
    {
        return (int) $this->external ? $this->Tercero : $this->Funcionario;
    }

    /**
     * obtiene la informacion preparada
     *
     * @return object
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    public function prepareData()
    {
        if (!$this->PreparedPublicUserData) {
            $this->PreparedPublicUserData = new PreparedPublicUserData($this);
        }

        return $this->PreparedPublicUserData->getPreparedData();
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
                'fk_act_planning'
            ],
            'date' => ['created_at', 'updated_at'],
            'table' => 'act_document_user',
            'primary' => 'idact_document_user',
            'relations' => [
                'Funcionario' => [
                    'model' => Funcionario::class,
                    'attribute' => 'idfuncionario',
                    'primary' => 'identification',
                    'relation' => self::BELONGS_TO_ONE
                ],
                'Tercero' => [
                    'model' => Tercero::class,
                    'attribute' => 'idtercero',
                    'primary' => 'identification',
                    'relation' => self::BELONGS_TO_ONE
                ],
            ]
        ];
    }

    protected function beforeCreate()
    {
        if (!$this->created_at) {
            $this->created_at = date('Y-m-d H:i:s');
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
}
