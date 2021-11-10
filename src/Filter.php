<?php

/**
 * Predefined filter
 * @package iqomp/validator
 * @version 2.3.0
 */

namespace Iqomp\Validator;

class Filter
{
    public static function array($value)
    {
        return is_null($value) ? $value : (array)$value;
    }

    public static function boolean($value)
    {
        return is_null($value) ? $value : (bool)$value;
    }

    public static function float($value)
    {
        return is_null($value) ? $value : (float)$value;
    }

    public static function integer($value)
    {
        return is_null($value) ? $value : (int)$value;
    }

    public static function json_encode($value)
    {
        return is_null($value) ? $value : json_encode($value);
    }

    public static function lowercase($value)
    {
        return is_null($value) ? $value : strtolower($value);
    }

    public static function object($value)
    {
        return is_null($value) ? $value : (object)$value;
    }

    public static function round($value, $options)
    {
        if (is_null($value)) {
            return $value;
        }
        return is_int($options) ? round($value, $options) : round($value);
    }

    public static function string($value)
    {
        return is_null($value) ? $value : (string)$value;
    }

    public static function ucwords($value)
    {
        return is_null($value) ? $value : ucwords($value);
    }

    public static function uppercase($value)
    {
        return is_null($value) ? $value : strtoupper($value);
    }
}
