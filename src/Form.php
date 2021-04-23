<?php

/**
 * Form object for validator
 * @package iqomp/validator
 * @version 2.0.0
 */

namespace Iqomp\Validator;

class Form
{
    protected $result;

    protected $errors = [];
    protected $form;
    protected $rules;

    protected function getBody()
    {
        $result = [];

        $body = file_get_contents('php://input');
        $type = $_SERVER['CONTENT_TYPE'];

        if (false !== strstr($type, ';')) {
            $type = explode(';', $type)[0];
        }

        if (false !== strstr($type, '/')) {
            $type = explode('/', $type)[1];
        }

        $type = strtolower($type);

        if ($type === 'json') {
            $body = json_decode($body);
            foreach ($body as $k => $v) {
                $result[$k] = $v;
            }
        }

        foreach ($_FILES as $k => $v) {
            $result[$k] = $v;
        }

        foreach ($_POST as $k => $v) {
            $result[$k] = $v;
        }

        foreach ($_GET as $k => $v) {
            $result[$k] = $v;
        }

        return (object)$result;
    }

    public function __construct(string $name)
    {
        $this->result = (object)[];
        $this->form = $name;

        $form = config('form.forms.' . $name);
        if (!$form) {
            $msg = 'Form named `' . $name . '` not registered';
            throw new FormNotRegisteredException($msg);
        }

        $this->rules = $form;
        foreach ($this->rules as $name => $conf) {
            $conf['name'] = $name;
            $this->rules[$name] = $conf;
        }
    }

    public function addError(string $field, string $code, string $text): void
    {
        $error = (object)[
            'field' => $field,
            'code'  => $code,
            'text'  => $text
        ];

        $this->errors[$field] = $error;
    }

    public function getError(string $field): ?object
    {
        return $this->errors[$field] ?? null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getName(): string
    {
        return $this->form;
    }

    public function getResult(): ?object
    {
        return $this->result;
    }

    public function hasError(): bool
    {
        return !!$this->errors;
    }

    public function validate(object $object = null): ?object
    {
        if (!$object) {
            $object = $this->getBody();
        }

        list($result, $error) = Validator::validate($this->rules, $object);

        $this->result = (object)$result;

        if ($error) {
            $this->errors = $error;
            return null;
        }

        return $this->result;
    }
}
