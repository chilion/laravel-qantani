<?php

namespace CJSDevelopment;

use CJSDevelopment\XmlBehavior;
use CJSDevelopment\config\Configuration;

class Execution
{
    public static function executeRequest($function, $parameters, $look_for = array())
    {
        $config = new Configuration();

        $data = array(
            'Transaction' => array(
                'Action' => array(
                    'Name' => $function,
                    'Version' => 1,
                    'ClientVersion' => $config->__get("api_url"),
                ),
                'Parameters' => $parameters,
                'Merchant' => array(
                    'ID' => config("qantani.merchant_id"),
                    'Key' => config("qantani.merchant_key"),
                    'Checksum' => self::_checksum($parameters),
                ),
            )
        );

        $xml = XmlBehavior::_encodeXML($data);


        $ch = curl_init($config->__get("api_url"));
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_URL, $config->__get("api_url"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . urlencode($xml));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $returndata = curl_exec($ch);
        $returndata = trim($returndata);

        return XmlBehavior::_decodeXML($returndata);


    }

    private static function _checksum($parameters)
    {
        ksort($parameters);
        $checksum = '';
        foreach ($parameters as $k => $v) {
            $checksum .= $v;
        }

        $lines = array();

        return sha1($checksum . implode('', $lines) . config("qantani.merchant_secret"));
    }
}