<?php

namespace CJSDevelopment;

class Qantani
{
    public function __construct()
    {
    }

    public static function getBanks($parameters = [])
    {
        $data = Execution::executeRequest('IDEAL.GETBANKS', $parameters, ['Response', 'Banks', 'Bank']);

        $status = $data['Response']['Status']['value'];

        if ($status != 'OK') {
            return false;
        }

        foreach ($data['Response']['Banks']['Bank'] as $item) {
            $res[] = [
                'name'  => $item['Name']['value'],
                'id'    => $item['Id']['value'],
            ];
        }

        return $res;
    }
}
