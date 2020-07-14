<?php


namespace Saia\Actas\controllers;


use Saia\controllers\modulo\ActionData;
use Saia\controllers\modulo\INewAction;
use Saia\models\Funcionario;

class NewScheduleAction implements INewAction
{

    /**
     * @inheritDoc
     */
    public function getActionData(Funcionario $Funcionario): ?ActionData
    {
        $params = [
            'views/modules/actas/dist/newActionSchedule/index.html',
            'Agendar reunión',
            [],
            ActionData::TYPE_IFRAME_MODAL,
        ];
        return new ActionData(...$params);
    }
}