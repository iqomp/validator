<?php

/**
 * Validator rules
 * @package iqomp/validator
 * @version 2.2.1
 */

namespace Iqomp\Validator;

class Rule
{
    protected static function getPropValue(object $object, string $fields)
    {
        $obj  = clone $object;
        $keys = explode('.', $fields);

        foreach ($keys as $ky) {
            if (is_array($obj)) {
                $obj = $obj[$ky] ?? null;
            } elseif (is_object($obj)) {
                $obj = $obj->$ky ?? null;
            }

            if (!$obj) {
                return $obj;
            }

            if (!is_object($obj) && !is_array($obj)) {
                return $obj;
            }
        }

        return $obj;
    }

    protected static function isIndexedArray(array $arr): bool
    {
        if (is_null($arr)) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    public static function array($val, $opts): ?array
    {
        if (is_null($val)) {
            return null;
        }

        if (!is_array($val)) {
            return ['1.0'];
        }

        if ($opts === true) {
            return null;
        }

        $indexed = self::isIndexedArray($val);

        if ($opts === 'indexed' && !$indexed) {
            return ['1.1'];
        }

        if ($opts === 'assoc' && $indexed) {
            return ['1.2'];
        }

        return null;
    }

    public static function bool($val) {
        if (is_null($val)) {
            return null;
        }

        if (is_bool($val)) {
            return null;
        }

        return ['29.0'];
    }

    public static function callback($val, $opts, $obj, $fld, $rules): ?array
    {
        if (is_null($val)) {
            return null;
        }

        $handler = explode('::', $opts);
        $class   = $handler[0];
        $method  = $handler[1];

        return $class::$method($val, $opts, $obj, $fld, $rules);
    }

    public static function date($val, $opts, $obj): ?array
    {
        if (is_null($val)) {
            return null;
        }

        $date = date_create_from_format($opts['format'], $val);
        if (false === $date) {
            return ['2.0'];
        }
        $value_format = date_format($date, $opts['format']);
        // if ($value_format != $val) {
        //     return ['2.1'];
        // }

        $value_time = date_create_from_format($opts['format'], $value_format);
        $value_time = $value_time->getTimestamp();

        $min = null;

        if (isset($opts['min-field'])) {
            $min = strtotime($obj->{$opts['min-field']} ?? 'now');
        }

        if (isset($opts['min'])) {
            $min = $min ? strtotime($opts['min'], $min) : strtotime($opts['min']);
        }

        if ($min) {
            $min_time = date($opts['format'], $min);
            $min_time = date_create_from_format($opts['format'], $min_time);
            $min_time = $min_time->getTimestamp();

            if ($min_time > $value_time) {
                return ['2.1'];
            }
        }

        $max = null;
        if (isset($opts['max-field'])) {
            $max = strtotime($obj->{$opts['max-field']} ?? 'now');
        }
        if (isset($opts['max'])) {
            $max = $max ? strtotime($opts['max'], $max) : strtotime($opts['max']);
        }
        if ($max) {
            $max_time = date($opts['format'], $max);
            $max_time = date_create_from_format($opts['format'], $max_time);
            $max_time = $max_time->getTimestamp();
            if ($max_time < $value_time) {
                return ['2.2'];
            }
        }

        return null;
    }

    public static function email($val): ?array
    {
        if (is_null($val)) {
            return null;
        }

        $email = filter_var($val, FILTER_VALIDATE_EMAIL);
        if (false === $email) {
            return ['3.0'];
        }
        return null;
    }

    public static function empty($val, $opts): ?array
    {
        if (is_null($val)) {
            return null;
        }

        if ($opts && $val) {
            return ['21.1'];
        } elseif (!$opts && !$val) {
            return ['21.0'];
        }

        return null;
    }

    public static function equalsTo($val, $opts, $obj, $fld, $rules): ?array
    {
        $reff_value = $obj->{$opts} ?? null;

        if ($val !== $reff_value) {
            return ['26.1'];
        }
        return null;
    }

    public static function file($val, $opts, $obj, $fld): ?array
    {
        $file = $_FILES[$fld] ?? null;
        if (!$file) {
            return null;
        }

        if ($val !== $file) {
            return ['28.0'];
        }

        return null;
    }

    public static function in($val, $opts): ?array
    {
        if (is_null($val)) {
            return null;
        }

        if (!in_array($val, $opts)) {
            return ['4.0'];
        }

        return null;
    }

    public static function ip($val, $opts): ?array
    {
        if (is_null($val)) {
            return null;
        }

        $rules = [
            '5.0' => [true, null],
            '5.1' => ['4',  FILTER_FLAG_IPV4],
            '5.2' => ['6',  FILTER_FLAG_IPV6]
        ];

        foreach ($rules as $err => $rule) {
            if ($rule[0] != $opts) {
                continue;
            }

            if (is_bool($rule[0]) && $opts !== $rule[0]) {
                continue;
            }

            $result = false;
            if ($rule[1]) {
                $result = filter_var($val, FILTER_VALIDATE_IP, $rule[1]);
            } else {
                $result = filter_var($val, FILTER_VALIDATE_IP);
            }

            if (false !== $result) {
                return null;
            }

            return [$err];
        }
    }

    public static function json($val): ?array
    {
        if (is_null($val)) {
            return null;
        }

        $tmp = json_decode($val);
        if (json_last_error() === JSON_ERROR_NONE) {
            return null;
        }

        return ['23.1'];
    }

    public static function length($val, $opts): ?array
    {
        if (is_null($val)) {
            return null;
        }

        if (is_string($val)) {
            $len = strlen($val);
        } elseif (is_array($val)) {
            $len = count($val);
        }

        if (isset($opts['min']) && $len < $opts['min']) {
            return ['6.0'];
        }

        if (isset($opts['max']) && $len > $opts['max']) {
            return ['6.1'];
        }

        return null;
    }

    public static function notin($val, $opts): ?array
    {
        if (is_null($val)) {
            return null;
        }

        if (in_array($val, $opts)) {
            return ['7.0'];
        }
        return null;
    }

    public static function numeric($val, $opts, $obj, $fname, $rules): ?array
    {
        if (is_null($val)) {
            return null;
        }

        if (!is_numeric($val)) {
            return ['8.0'];
        }

        if (is_array($opts)) {
            if (isset($opts['min']) && $val < $opts['min']) {
                return ['8.1'];
            }

            if (isset($opts['max']) && $val > $opts['max']) {
                return ['8.2'];
            }

            // TODO
            // make this work
            // if (isset($opts['decimal'])) {
                // return ['8.3'];
            // }
        }

        return null;
    }

    public static function object($val): ?array
    {
        if (is_null($val)) {
            return null;
        }

        if (!is_object($val)) {
            return ['9.0'];
        }

        return null;
    }

    public static function regex($val, $opts): ?array
    {
        if (!$val) {
            return null;
        }

        if (!preg_match($opts, $val)) {
            return ['10.0'];
        }
        return null;
    }

    public static function required($val): ?array
    {
        if (is_null($val)) {
            return ['11.0'];
        }
        return null;
    }

    public static function requiredOn($val, $opts, $obj): ?array
    {
        if (!is_null($val)) {
            return null;
        }

        foreach ($opts as $ofield => $cond) {
            $operator   = $cond['operator'];
            $expect_val = $cond['expected'];
            $other_val  = self::getPropValue($obj, $ofield) ?? null;

            $match = false;

            switch ($operator) {
                case '=':
                    $match = $other_val == $expect_val;
                    break;
                case '!=':
                    $match = $other_val != $expect_val;
                    break;
                case '>':
                    $match = $other_val > $expect_val;
                    break;
                case '<':
                    $match = $other_val < $expect_val;
                    break;
                case '>=':
                    $match = $other_val >= $expect_val;
                    break;
                case '<=':
                    $match = $other_val <= $expect_val;
                    break;
                case 'in':
                    $match = in_array($other_val, $expect_val);
                    break;
                case '!in':
                    $match = !in_array($other_val, $expect_val);
                    break;
            }

            if ($match) {
                return ['11.0'];
            }
        }

        return null;
    }

    public static function text($val, $opts): ?array
    {
        if (!$val) {
            return null;
        }

        if (!is_string($val)) {
            return ['12.0'];
        }

        if ($opts === true) {
            return null;
        }

        if ($opts === 'slug' && !preg_match('!^[a-z0-9-_]+$!', $val)) {
            return ['12.1'];
        }

        if ($opts === 'alnumdash' && !preg_match('!^[a-zA-Z0-9-]+$!', $val)) {
            return ['12.2'];
        }

        if ($opts === 'alpha' && !preg_match('!^[a-zA-Z]+$!', $val)) {
            return ['12.3'];
        }

        if ($opts === 'alnum' && !preg_match('!^[a-zA-Z0-9]+$!', $val)) {
            return ['12.4'];
        }

        // match the regex
        if (substr($opts, 0, 1) === substr($opts, -1)) {
            if (!preg_match($opts, $val)) {
                return ['12.5'];
            }
        }

        return null;
    }

    public static function url($val, $opts): ?array
    {
        if (!$val) {
            return null;
        }

        $fvu  = FILTER_VALIDATE_URL;
        $ffpr = FILTER_FLAG_PATH_REQUIRED;
        $ffqr = FILTER_FLAG_QUERY_REQUIRED;

        if (!filter_var($val, $fvu)) {
            return ['13.0'];
        }
        if (!is_array($opts)) {
            return null;
        }

        if (isset($opts['path']) && !filter_var($val, $fvu, $ffpr)) {
            return ['13.1'];
        }

        if (isset($opts['query'])) {
            if (!filter_var($val, $fvu, $ffqr)) {
                return ['13.2'];
            }

            if (is_string($opts['query'])) {
                $opts['query'] = (array)$opts['query'];
            }

            if (is_array($opts['query'])) {
                $query = parse_url($val, PHP_URL_QUERY);
                if (!$query) {
                    return ['13.2'];
                }

                parse_str($query, $qry);

                foreach ($opts['query'] as $val) {
                    if (!isset($qry[$val])) {
                        return ['13.3'];
                    }
                }
            }
        }

        return null;
    }
}
