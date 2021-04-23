<?php

/**
 * Config provider
 * @package iqomp/validator
 * @version 2.0.0
 */

namespace Iqomp\Validator;

class ConfigProvider
{
    protected function getPublishedFiles(): array
    {
        $base = dirname(__DIR__) . '/publish';
        $files = $this->scanDir($base, '/');
        $result = [];

        foreach ($files as $file) {
            $source = $base . $file;
            $target = BASE_PATH . $file;

            $result[] = [
                'id' => $file,
                'description' => 'Publish file of ' . $file,
                'source' => $source,
                'destination' => $target
            ];
        }

        return $result;
    }

    protected function scanDir(string $base, string $path): array
    {
        $base_path = chop($base . $path, '/');
        $files = array_diff(scandir($base_path), ['.', '..']);
        $result = [];

        foreach ($files as $file) {
            $file_path = trim($path . '/' . $file, '/');
            $file_base = $base_path . '/' . $file;

            if (is_dir($file_base)) {
                $sub_files = $this->scanDir($base, '/' . $file_path);
                if ($sub_files) {
                    $result = array_merge($result, $sub_files);
                }
            } else {
                $result[] = '/' . $file_path;
            }
        }

        return $result;
    }

    public function __invoke()
    {
        return [
            'form' => [],
            'validator' => [
                'errors' => [
                    '1.0' => 'not an array',
                    '1.1' => 'not indexed array',
                    '1.2' => 'not assoc array',
                    '2.0' => 'not a date',
                    '2.1' => 'the date too early',
                    '2.2' => 'the date too far',
                    '2.3' => 'wrong date format',
                    '3.0' => 'not an email',
                    '4.0' => 'not in array',
                    '5.0' => 'not an ip',
                    '5.1' => 'not an ipv4',
                    '5.2' => 'not an ipv6',
                    '6.0' => 'too short',
                    '6.1' => 'too long',
                    '7.0' => 'in array',
                    '8.0' => 'not numeric',
                    '8.1' => 'too less',
                    '8.2' => 'too great',
                    '8.3' => 'decimal not match',
                    '9.0' => 'not an object',
                    '10.0' => 'not match',
                    '11.0' => 'required',
                    '12.0' => 'not a text',
                    '12.1' => 'not a slug',
                    '12.2' => 'not an alnumdash',
                    '12.3' => 'not an alpha',
                    '12.4' => 'not an alnum',
                    '13.0' => 'not an url',
                    '13.1' => 'dont have path',
                    '13.2' => 'dont have query',
                    '13.3' => 'require query not present',
                    '21.0' => 'is empty',
                    '21.1' => 'is not empty',
                    '23.1' => 'is not valid json string',

                    '25.0' => 'is not in acceptable value',
                    '25.1' => 'is not in acceptable list values',
                    '25.2' => 'is not match with requested value',

                    '26.1' => 'is not equal',
                    '28.0' => 'is not file'
                ],
                'filters' => [
                    'array'     => 'Iqomp\\Validator\\Filter::array',
                    'boolean'   => 'Iqomp\\Validator\\Filter::boolean',
                    'float'     => 'Iqomp\\Validator\\Filter::float',
                    'integer'   => 'Iqomp\\Validator\\Filter::integer',
                    'lowercase' => 'Iqomp\\Validator\\Filter::lowercase',
                    'object'    => 'Iqomp\\Validator\\Filter::object',
                    'round'     => 'Iqomp\\Validator\\Filter::round',
                    'string'    => 'Iqomp\\Validator\\Filter::string',
                    'ucwords'   => 'Iqomp\\Validator\\Filter::ucwords',
                    'uppercase' => 'Iqomp\\Validator\\Filter::uppercase'
                ],
                'validators' => [
                    'array'     => 'Iqomp\\Validator\\Rule::array',
                    'callback'  => 'Iqomp\\Validator\\Rule::callback',
                    'date'      => 'Iqomp\\Validator\\Rule::date',
                    'email'     => 'Iqomp\\Validator\\Rule::email',
                    'empty'     => 'Iqomp\\Validator\\Rule::empty',
                    'equals_to' => 'Iqomp\\Validator\\Rule::equalsTo',
                    'file'      => 'Iqomp\\Validator\\Rule::file',
                    'in'        => 'Iqomp\\Validator\\Rule::in',
                    'ip'        => 'Iqomp\\Validator\\Rule::ip',
                    'json'      => 'Iqomp\\Validator\\Rule::json',
                    'length'    => 'Iqomp\\Validator\\Rule::length',
                    'object'    => 'Iqomp\\Validator\\Rule::object',
                    'notin'     => 'Iqomp\\Validator\\Rule::notin',
                    'numeric'   => 'Iqomp\\Validator\\Rule::numeric',
                    'regex'     => 'Iqomp\\Validator\\Rule::regex',
                    'required'  => 'Iqomp\\Validator\\Rule::required',
                    'req_on'    => 'Iqomp\\Validator\\Rule::requiredOn',
                    'text'      => 'Iqomp\\Validator\\Rule::text',
                    'url'       => 'Iqomp\\Validator\\Rule::url'
                ]
            ],
            'publish' => $this->getPublishedFiles()
        ];
    }
}
