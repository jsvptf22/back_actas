<?php

namespace Saia\Actas\formatos\tema;

class FtTema extends FtTemaProperties
{
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * refresca los temas del acta
     *
     * @param FtActa $FtActa
     * @param array $topicList
     * @param array $topicListDescriptions
     * @return array
     * @author jhon sebastian valencia <jhon.valencia@cerok.com>
     * @date 2019-11-26
     */
    public static function refreshItems($FtActa, $topicList, $topicListDescriptions)
    {
        $defaultFtData = [
            'dependencia' => $FtActa->dependencia,
        ];

        self::executeUpdate([
            'estado' => 0
        ], [
            'estado' => 1,
            'ft_acta' => $FtActa->getPK()
        ]);

        $Formato = \Formato::findByAttributes([
            'nombre' => 'tema'
        ]);
        $GuardarFtController = new \GuardarFtController($Formato->getPK());

        foreach ($topicList as $topic) {
            $FtTema = self::findByAttributes([
                'idft_tema' => $topic->id,
                'ft_acta' => $FtActa->getPK()
            ]);

            $data = array_merge($defaultFtData, [
                'estado' => 1,
                'nombre' => $topic->label,
                'ft_acta' => $FtActa->getPK(),
                'desarrollo' => ''
            ]);

            foreach ($topicListDescriptions as $key => $item) {
                if ($topic->id == $item->topic) {
                    $data['desarrollo'] = $item->description;
                    unset($topicListDescriptions[$key]);
                    break;
                }
            }

            if ($FtTema) {
                $GuardarFtController->edit($data, $FtTema->documento_iddocumento);
            } else {
                $GuardarFtController->create($data);
            }
        }

        $topics = self::findAllByAttributes([
            'estado' => 1,
            'ft_acta' => $FtActa->getPK()
        ]);

        $response = [];
        foreach ($topics as $key => $FtTema) {
            array_push($response, [
                'id' => $FtTema->getPK(),
                'name' => $FtTema->nombre,
                'description' => $FtTema->descripcion
            ]);
        }

        return $response;
    }
}
