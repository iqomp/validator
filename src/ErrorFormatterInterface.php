<?php

/**
 * Error Formatter Interface
 * @package iqomp/validator
 * @version 1.0.0
 */

namespace Iqomp\Validator;

interface ErrorFormatterInterface
{
    /**
     * Format single error object
     * @param object $err The error object to format
     * @param string $fld The field name of the error
     * @param array $errs All error message
     * @param object $obj Current object that being validated.
     * @return mixed.
     */
    public static function format(object $err, string $fld, array $errs, object $obj);
}
