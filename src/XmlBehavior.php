<?php

namespace CJSDevelopment;

class XmlBehavior
{
    public static function _encodeXML($data, $level = 0)
    {
        $xml = ($level == 0) ? '<?xml version="1.0" encoding="UTF-8"?'.'>'."\n" : '';

        foreach ($data as $k => $v) {
            $xml .= str_repeat("\t", $level);
            if (preg_match('/^(.+)\.(\d+)$/', $k, $matches)) {
                $k = $matches[1];
            }
            $xml .= '<'.$k;
            $children = [];
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if (substr($k2, 0, 5) == 'attr:') {
                        $xml .= ' '.substr($k2, 5).'="'.$v2.'"';
                        unset($v[$k2]);
                    }
                }
            }
            if (is_array($v) && count($v)) {
                $xml .= '>'."\n";
                $xml .= self::_encodeXML($v, $level + 1);
                $xml .= str_repeat("\t", $level).'</'.$k.'>'."\n";
            } else {
                if ($v) {
                    $xml .= '>'.htmlentities($v).'</'.$k.'>'."\n";
                } else {
                    $xml .= ' />'."\n";
                }
            }
        }

        return $xml;
    }

    public static function _decodeXML($contents)
    {
        $get_attributes = false;

        if (!$contents) {
            return [];
        }
        $priority = '';

        if (!function_exists('xml_parser_create')) {
            return [];
        }

        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) {
            return;
        }

        $xml_array = [];
        $parents = [];
        $opened_tags = [];
        $arr = [];

        $current = &$xml_array;

        $repeated_tag_index = [];
        foreach ($xml_values as $data) {
            unset($attributes, $value);

            extract($data);

            $result = [];
            $attributes_data = [];

            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }

            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val;
                    }
                }
            }

            if ($type == 'open') {
                $parent[$level - 1] = &$current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    if ($attributes_data) {
                        $current[$tag.'_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level] = 1;

                    $current = &$current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        $repeated_tag_index[$tag.'_'.$level]++;
                    } else {
                        $current[$tag] = [$current[$tag],$result];
                        $repeated_tag_index[$tag.'_'.$level] = 2;

                        if (isset($current[$tag.'_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag.'_'.$level] - 1;
                    $current = &$current[$tag][$last_item_index];
                }
            } elseif ($type == 'complete') {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if ($priority == 'tag' and $attributes_data) {
                        $current[$tag.'_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level].'_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;
                    } else {
                        $current[$tag] = [$current[$tag],$result];
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag.'_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }

                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level].'_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;
                    }
                }
            } elseif ($type == 'close') {
                $current = &$parent[$level - 1];
            }
        }

        return($xml_array);
    }
}
