<?php

/**
 * Validation error message formatter
 * @package iqomp/validator
 * @version 1.0.0
 */

namespace Iqomp\Validator;

class ErrorFormatter implements ErrorFormatterInterface
{
    public static function format(object $err, string $fld, array $errs, object $obj)
    {
        return $err;
    }
}
