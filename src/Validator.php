<?php

/**
 * Validation processor
 * @package iqomp/validator
 * @version 1.0.0
 */

namespace Iqomp\Validator;

use Iqomp\Config\Fetcher as Config;
use Iqomp\Locale\Locale;

class Validator
{
    protected static $filters = [];
    protected static $formatter;
    protected static $rules   = [];
    protected static $trans   = [];

    private static function buildRules(): void
    {
        $conf = Config::get('validator');
        self::$rules     = $conf['validators'];
        self::$filters   = $conf['filters'];
        self::$trans     = $conf['errors'];

        if (!isset($conf['formatter'])) {
            $conf['formatter'] = 'Iqomp\\Validator\\ErrorFormatter';
        }

        self::setErrorFormatter($conf['formatter']);
    }

    protected static function buildError(array $data): object
    {
        $valid      = $data['valid'];
        $rules      = $data['rules'];
        $rule       = $data['rule'];
        $value      = $data['value'];
        $field      = $data['field'];
        $validation = $data['validation'];
        $parent     = $data['parent'];
        $err_code   = $valid[0];
        $params     = $valid[1] ?? [];

        $params['field'] = $field;
        $params[$rule]   = $validation['rules'][$rule];
        $params['value'] = $value;
        $params['label'] = $field;

        if (isset($validation['label'])) {
            $params['label'] = $validation['label'];
        }

        if (is_object($value)) {
            $params['value'] = '::object';
        } elseif (is_array($value)) {
            $params['value'] = '::array';
        }

        foreach ($validation as $key => $val) {
            if (in_array($key, ['rules', 'filters', 'children'])) {
                continue;
            }

            $params[$key] = $val;
        }

        $result = (object)[
            'field'   => ($parent ? $parent . '.' : '') . $field,
            'code'    => $err_code,
            'text'    => '',
            'options' => $validation
        ];

        $lang_key = self::$trans[$err_code] ?? '';
        if (!$lang_key && isset($valid[2])) {
            $lang_key = $valid[2];
        }

        // custom translation key
        if (($validation['message'][$rule] ?? null)) {
            $lang_key = $validation['message'][$rule];
        }

        $text = Locale::translate($lang_key, $params, 'validator');
        $result->text = $text;

        return $result;
    }

    protected static function formatError(array $errors, object $object): array
    {
        $fmt = self::$formatter;

        foreach ($errors as $field => $error) {
            $res = $fmt::format($error, $field, $errors, $object);
            $errors[$field] = $res;
        }

        return $errors;
    }

    public static function getErrorFormatter(): string
    {
        return self::$formatter;
    }

    public static function setErrorFormatter($fmt): void
    {
        self::$formatter = $fmt;
    }

    public static function validate(
        array $rules,
        object $obj,
        string $par = ''
    ): array {
        if (!self::$rules) {
            self::buildRules();
        }

        $result = [$obj, null];

        $new_object = (object)[];
        $new_errors = [];

        foreach ($rules as $fname => $field) {
            $rules       = $field['rules']    ?? [];
            $filters     = $field['filters']  ?? [];
            $children    = $field['children'] ?? null;
            $next_parent = ($par ? $par . '.' : '') . $fname;

            $undifined   = !property_exists($obj, $fname);
            $value       = !$undifined ? $obj->$fname : null;

            $is_valid = true;

            foreach ($rules as $rname => $ropt) {
                if (!isset(self::$rules[$rname])) {
                    $msg = 'Validation rule `' . $rname . '` not registered';
                    throw new RuleNotRegisteredException($msg);
                }

                $handler = explode('::', self::$rules[$rname]);
                $class   = $handler[0];
                $method  = $handler[1];

                $valid = $class::$method($value, $ropt, $obj, $fname, $rules);
                if (is_null($valid)) {
                    continue;
                }

                $is_valid = false;

                $new_errors[$fname] = self::buildError([
                    'valid'         => $valid,
                    'rules'         => $rules,
                    'rule'          => $rname,
                    'value'         => $value,
                    'field'         => $fname,
                    'parent'        => $par,
                    'validation'    => $field
                ]);
                break;
            }

            if ($is_valid) {
                foreach ($filters as $name => $fopt) {
                    if (!isset(self::$filters[$name])) {
                        $msg = 'Validation filter `' . $name . '` not registered';
                        throw new FilterNotRegisteredException($msg);
                    }

                    $handler = explode('::', self::$filters[$name]);
                    $class   = $handler[0];
                    $method  = $handler[1];
                    $value   = $class::$method($value, $fopt, $obj, $fname, $filters);
                }

                // apply children
                if ($children && $value) {
                    // indexed array
                    if (isset($children['*'])) {
                        $cvalidation = $children['*'];

                        foreach ($value as $idx => $vitem) {
                            $vobj = (object)[$idx => $vitem];
                            $vval = [$idx => $cvalidation];

                            list($vres, $verr) = self::validate($vval, $vobj, $next_parent);
                            $value[$idx] = $vres->$idx;
                            if ($verr) {
                                foreach ($verr as $key => $val) {
                                    $new_errors[$fname . '.' . $key] = $val;
                                }
                            }
                        }
                    } else {
                        // non indexed array
                        $is_array = is_array($value);
                        list($cres, $cerr) = self::validate($children, (object)$value, $next_parent);
                        $value = $cres;
                        if ($is_array) {
                            $value = (array)$value;
                        }

                        if ($cerr) {
                            foreach ($cerr as $key => $val) {
                                $new_errors[$fname . '.' . $key] = $val;
                            }
                        }
                    }
                }
            }

            if (!$undifined) {
                $new_object->$fname = $value;
            }
        }

        $result[0] = $new_object;
        $result[1] = self::formatError($new_errors, $new_object);

        return $result;
    }
}
