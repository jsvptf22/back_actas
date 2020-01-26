<?php

namespace Saia\Actas\formatos\agendamiento_acta;

use Saia\core\model\ModelFormat;

class FtAgendamientoActaProperties extends ModelFormat
{
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    protected function defaultDbAttributes()
    {
        return [
            'safe' => [
                'date',
				'dependencia',
				'documento_iddocumento',
				'duration',
				'encabezado',
				'firma',
				'idft_agendamiento_acta',
				'state',
				'subject' 
            ],
            'date' => ['date'],
            'table' => 'ft_agendamiento_acta',
            'primary' => 'idft_agendamiento_acta'
        ];
    }

    protected function defineMoreAttributes()
    {
        return [];
    }
}