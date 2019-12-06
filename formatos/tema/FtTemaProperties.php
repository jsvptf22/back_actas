<?php

namespace Saia\Actas\formatos\tema;

class FtTemaProperties extends \ModelFormat
{
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    protected function defaultDbAttributes()
    {
        return [
            'safe' => [
                'dependencia',
				'desarrollo',
				'documento_iddocumento',
				'encabezado',
				'estado',
				'firma',
				'ft_acta',
				'idft_tema',
				'nombre' 
            ],
            'date' => [],
            'table' => 'ft_tema',
            'primary' => 'idft_tema'
        ];
    }
}