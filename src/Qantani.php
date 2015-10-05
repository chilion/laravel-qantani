<?php

namespace CJSDevelopment;


use CJSDevelopment\XmlBehavior;
use CJSDevelopment\Execution;

class Qantani
{
    public function __construct() {

    }

    public static function getBanks($parameters = array()){
        $data = Execution::executeRequest('IDEAL.GETBANKS', $parameters, array('Response', 'Banks', 'Bank'));

        $status = $data["Response"]["Status"]["value"];

        if ($status != "OK") {
            return false;
        }

        foreach($data["Response"]["Banks"]["Bank"] as $item){
            $res[] = [
                'name'  => $item['Name']['value'],
                'id'    => $item['Id']['value']
            ];
        }

        return $res;
    }



}