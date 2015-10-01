<?php

namespace CJSDevelopment;

use \SimpleXMLElement;

class Qantani
{
     public function __construct() {
        return $this;
    }


    public static $apiUrl = 'https://www.qantanipayments.com/api/'; /** location of the api */
    static public $version = '1.1'; /** CJSDevelopment / Qantani version */

    public static function getBanks($parameters = array()){
        $data = self::executeRequest('IDEAL.GETBANKS', $parameters, array('Response', 'Banks', 'Bank'));
        $res = [];

        foreach($data as $item){
            $res[] = [
                'Name'  => $item['Name']['value'],
                'Id'    => $item['Id']['value']
            ];
        }
        return $res;
    }


    private static function executeRequest($function, $parameters, $look_for = array()){

        $data = array(
            'Transaction' => array(
                'Action' => array(
                    'Name' => $function,
                    'Version' => 1,
                    'ClientVersion' => self::$version,
                ),
                'Parameters' => $parameters,
                'Merchant' =>array(
                    'ID' => config("qantani.merchant_id"),
                    'Key' => config("qantani.merchant_key"),
                    'Checksum' => parent::self()->_checksum($parameters),
                ),
            )
        );

        $ch = curl_init(self::$apiUrl);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_URL, self::$apiUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $returndata = curl_exec($ch);
        $returndata = trim($returndata);


    }

    private function _checksum($parameters){
        ksort($parameters);
        $checksum = '';
        foreach($parameters as $k=>$v){
            $checksum .= $v;
        }

        $lines = array();


        // insert products
        if (count($this->_products)){
            foreach($this->_products as $index=>$product){
                $lines[] .= $product['Amount'] . $product['Currency'] . $product['Description'] . $product['ID'] . $product['Price'] . $product['Vat'];
            }
        }

        // insert customer
        if (count($this->_customer)){
            ksort($this->_customer);
            $line = '';
            foreach($this->_customer as $v){
                $line .= $v;
            }
            $lines[] = $line;
        }

        return sha1($checksum . implode('', $lines) . config("qantani.merchant_key"));
    }

}