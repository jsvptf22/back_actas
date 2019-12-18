<?php

namespace Saia\Actas\controllers;

use Saia\Actas\models\ActDocumentUser;

class PreparedPublicUserData
{

    /**
     * almacena la instancia de AcDocumentUser
     *
     * @var AcDocumentUser
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-12-07
     */
    protected $ActDocumentUser;


    /**
     * almacena la informacion preparada
     *
     * @var object
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    protected $prepare;


    public function __construct(ActDocumentUser $ActDocumentUser)
    {
        $this->ActDocumentUser = $ActDocumentUser;
    }

    /**
     * genera un objecto con datos especificos 
     * de la instancia ActDocumentUser
     *
     * @return object
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019
     */
    public function getPreparedData()
    {
        if (!$this->prepare) {
            $this->prepare = (object) [
                'id' => $this->ActDocumentUser->identification,
                'name' => $this->ActDocumentUser->getUser()->getName(),
                'text' => $this->ActDocumentUser->getUser()->getName(),
                'external' => $this->ActDocumentUser->external,
            ];
        }

        return $this->prepare;
    }
}
