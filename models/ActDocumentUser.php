<?php

namespace Saia\Actas\models;

use Saia\Actas\controllers\PublicUserPreparer;
use Saia\Core\model\Model;
use Saia\models\Tercero;
use Saia\models\vistas\VfuncionarioDc;

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
     * identifica una relacion de tipo secretario
     */
    const RELATION_ORGANIZER = 4;

    /**
     * identificador para el campo external
     * para personas internas
     */
    const INTERNAL = 0;

    /**
     * identificador para el campo external
     * para personas externas
     */
    const EXTERNAL = 1;

    /**
     * almacena la clase que prepara los datos para el cliente
     *
     * @var PublicUserPreparer
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019
     */
    protected ?PublicUserPreparer $PreparedPublicUserData = null;

    /**
     * ActDocumentUser constructor.
     *
     * @param null $id
     * @throws \Exception
     */
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
                'fk_ft_acta',
                'relation',
                'state',
                'identification',
                'external',
                'created_at',
                'updated_at',
            ],
            'date' => ['created_at', 'updated_at'],
            'table' => 'act_document_user',
            'primary' => 'idact_document_user',
            'relations' => [
                'VfuncionarioDc' => [
                    'model' => VfuncionarioDc::class,
                    'attribute' => 'iddependencia_cargo',
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

    /**
     * inactiva todas las relaciones de un documento
     * basadas en un tipo de relacion
     *
     * @param integer $fk_ft_acta
     * @param integer $relationType
     * @return boolean
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
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

    /**
     * crea o actualiza la relacion de un usuario con el documento
     *
     * @param integer $fk_ft_acta
     * @param object  $user
     * @param integer $relationType
     * @return void
     * @throws \Exception
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public static function updateUserRelation($fk_ft_acta, $user, $relationType)
    {
        ActDocumentUser::newRecord([
            'fk_ft_acta' => $fk_ft_acta,
            'state' => 1,
            'relation' => $relationType,
            'identification' => $user->id,
            'external' => $user->external ?? 1
        ]);
    }

    /**
     * obtiene el correo del usuario
     *
     * @return string
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-10
     */
    public function getUserEmail()
    {
        $Entity = $this->getUser();
        return $Entity instanceof VfuncionarioDc ?
            $Entity->email : $Entity->correo;
    }

    /**
     * obtiene la instancia del usuario
     *
     * @return VfuncionarioDc|Tercero
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function getUser()
    {
        return (int)$this->external ? $this->Tercero : $this->VfuncionarioDc;
    }

    /**
     * obtiene la informacion preparada
     *
     * @return object
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date   2019-12-07
     */
    public function prepareData()
    {
        if (!$this->PreparedPublicUserData) {
            $this->PreparedPublicUserData = new PublicUserPreparer($this);
        }

        return $this->PreparedPublicUserData->getPreparedData();
    }
}
