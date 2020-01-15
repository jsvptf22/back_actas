<?php

namespace Saia\Actas\formatos\acta;

use Saia\core\model\ModelFormat;

class FtActaProperties extends ModelFormat
{
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    protected function defaultDbAttributes()
    {
        return [
            'safe' => [
                'asistentes_externos',
				'asistentes_internos',
				'asunto',
				'dependencia',
				'documento_iddocumento',
				'encabezado',
				'estado',
				'fecha_final',
				'fecha_inicial',
				'firma',
				'fk_act_planning',
				'idft_acta',
				'room' 
            ],
            'date' => ['fecha_inicial',
				'fecha_final'],
            'table' => 'ft_acta',
            'primary' => 'idft_acta'
        ];
    }

    protected function defineMoreAttributes()
    {
        return [];
    }
}