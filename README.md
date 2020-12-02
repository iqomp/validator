# iqomp/validator

Simple and easy form/object validator. This module bring two usable class to
work with validation, which is `Iqomp\Validator\Validator` which can be used to
validate plain object againts list of rules in array. The second class is
`Iqomp\Validator\Form` that take nothing ( optionally user generated object to
validate ), and take the validation rule from private config. Not only validate,
the validator class can also apply filter to the object property to change modify
it.

The cache of all forms, validation rules, and validation filters is generated
during `composer update`. You should call this script for everytime some config
updated on your module.

## Installation

```bash
composer require iqomp/locale
```

## Validator

Class `Iqomp\Validator\Validator` can be use to validate object with developer
decide list of validation rules.

```php
use Iqomp\Validator\Validator;

$rules = [
    'name' => [
        'rules' => [
            'required' => true
        ],
        'filters' => [
            'string' => true
        ]
    ]
];
$object= (object)['name' => 'User'];
list($result, $errors) = Validator::validate($rules, $object);
```

Result `$result` is an object with properties taken from `$object` only if the
property has rule defined on `$rules`.

```
stdClass Object
(
    [name] => User
)
```

Result `$errors` is `property-(object)error` pair of array list of errors upon
validation. It can be an empty array on no error found.

```
Array
(
    [name] => stdClass Object
        (
            [field] => name
            [code] => 11.0
            [text] => This field is required
            [options] => Array
                (
                    [rules] => Array
                        (
                            [required] => 1
                        )

                    [filters] => Array
                        (
                            [string] => 1
                        )

                )

        )

)
```

### Method

#### static getErrorFormatter(): string

Get current custom error formatter

#### static setErrorFormatter(string $formatter): void

Set custom error formatter.

#### static function validate(array $rules, object $object): array

Validate the object againts list of rules. This action will return array with two
member, the first one is validated object, and the second one is list of exists
error or empty array if no error exists.

### Validator Rules

This is all validator rules define by this module.

#### array => true | assoc | indexed

Make sure the value is an array, optionally the validator can validate if it's
an assoc or indexed array:

```php
    // ...
    'rules' => [
        'array' => true // 'assoc', 'indexed'
    ]
    // ...
```

#### callback => Class::method

Validate the value with external class. The value of the rule should be class and
method of class handler:

```php
    // ...
    'rules' => [
        'callback' => 'Namespace\\Class::method'
    ]
    // ...
```

The callback should return indexed array as `[:code, :params, :text]`. Where `:code`
is error code, `:params` is list of parameters to send to translation based on
error code, and `:text` is final error message to use if exists. The `:code` will
be translated only if `:text` is not defined.

#### date => {format,min-field,min,max-field,max}

Make sure the value is a known date based on provided format. It can validate with
`min`, `max` date. The `min` and `max` can also be taken from other object field.

```php
    // ...
    'rules' => [
        'date' => [
            'format' => 'Y-m-d',
            'min' => 'now',
            // 'min-field' => 'date-start',
            'max' => '+12 days',
            // 'max-field' => 'date-end'
        ]
    ]
    // ...
```

#### email => true

Make sure the value is valid email.

```php
    // ...
    'rules' => [
        'email' => true
    ]
    // ...
```

#### empty => false

Make sure the value is not empty ( falsy ). This rule will set `null` as valid.
Use it with `required` rule to make sure it's posted and is not falsy.

```php
    // ...
    'rules' => [
        'empty' => false
    ]
    // ...
```

#### equals_to => field

Make sure the value is equals to other object property value. It can be used for
`new-password` field on change password action.

```php
    // ...
    'rules' => [
        'equals_to' => 'new-password'
    ]
    // ...
```

#### file => true

Make sure the field is `_FILES` property.

```php
    // ...
    'rules' => [
        'file' => true
    ]
    // ...
```

#### in => []

Make sure the value is one of defined list.

```php
    // ...
    'rules' => [
        'in' => ['one','two','three']
    ]
    // ...
```

#### ip => true | 4 | 6

Make sure the value is a valid ip address. Optionally, it can validate for IPv4
or IPv6

```php
    // ...
    'rules' => [
        'ip' => true // '4' | '6'
    ]
    // ...
```

#### json => true

Make sure the value is valid JSON string. It expect a `string`.

```php
    // ...
    'rules' => [
        'json' => true
    ]
    // ...
```

#### length => {min,max}

Make sure the value length is in accepted range. The value expecting a string or
an array. One of the rule properti `max` or `min` should defined.

```php
    // ...
    'rules' => [
        'length' => [
            'min' => 1,
            'max' => 12
        ]
    ]
    // ...
```

#### notin => []

Make sure the value is not one of defined list.

```php
    // ...
    'rules' => [
        'notin' => ['one','two','three']
    ]
    // ...
```

#### numeric => true | {min,max}

Make sure the value is numeric. Optionally pass option `min` and or `max`:

```php
    // ...
    'rules' => [
        // 'numeric' => true,
        'numeric' => [
            'min' => 1,
            'max' => 12
        ]
    ]
    // ...
```

#### object => true

Make sure the value is an object.

```php
    // ...
    'rules' => [
        'object' => true
    ]
    // ...
```

#### regex => '!x!'

Test string value againts regex.

```php
    // ...
    'rules' => [
        'regex' => '![0-9]+!'
    ]
    // ...
```

#### required => true

Make sure the value is present and is not null.

```php
    // ...
    'rules' => [
        'required' => true
    ]
    // ...
```

#### req_on => {field=>{operator,expected}}

Make sure the property is exists and is not null only if condition match. This rule
require parameter `operator` and `expected` to be exists for every other field.

The value of `operator` is one of `=`, `!=`, `>`, `<`, `<=`, `>=`, `in`, and `!in`.

```php
    // ...
    'rules' => [
        'req_on' => [
            'other_field' => [
                'operator' => '=',
                'expected' => 12
            ],
            'more_field' => [
                'operator' => 'in',
                'expected' => ['one','two','three']
            ]
        ]
    ]
    // ...
```

Above rules make sure the value of `object->other_field` is equal to 12 **AND**
the value of `object->more_field` is one of `one`, `two`, or `three`. Current
field is required only if both of the condition is match.

#### text => true | slug | alnumdash | alpha | alnum

Make sure the value is a text. Optionally follow accepted characters.

1. `true` Validate only the type of the value as string
1. `slug` Accept only characters `^[a-z0-9-_]+$`
1. `alnumdash` Accept only characters `^[a-zA-Z0-9-]+$`
1. `alpha` Accept only characters `^[a-zA-Z]+$`
1. `alnum` Accept only characters `^[a-zA-Z0-9]+$`

```php
    // ...
    'rules' => [
        'text' => true,
        // 'text' => 'slug'
    ]
    // ...
```

#### url => true | { path, query => true | [] }

Make sure the value is valid URL, optionally with path and some query string.

```php
    // ...
    'rules' => [
        'url' => true,
        'url' => [
            'path' => true,
            'query' => true,
            'query' => ['page','rpp','q']
        ]
    ]
    // ...
```

### Validator Filters

#### array => true

Convert the value to array.

```php
    // ...
    'filters' => [
        'array' => true
    ]
    // ...
```

#### boolean => true

Convert the value to boolean

```php
    // ...
    'filters' => [
        'boolean' => true
    ]
    // ...
```

#### float => true

Convert the value to float

```php
    // ...
    'filters' => [
        'float' => true
    ]
    // ...
```

#### integer

Convert the value to integer

```php
    // ...
    'filters' => [
        'integer' => true
    ]
    // ...
```

#### lowercase

Convert the value to lowercase

```php
    // ...
    'filters' => [
        'lowercase' => true
    ]
    // ...
```

#### object

Convert the value to object

```php
    // ...
    'filters' => [
        'object' => true
    ]
    // ...
```

#### round => true | decimal

Round the numeric value. Optionally set total decimal.

```php
    // ...
    'filters' => [
        // 'round' => true,
        'round' => 2
    ]
    // ...
```

#### string

Convert the value to string

```php
    // ...
    'filters' => [
        'string' => true
    ]
    // ...
```

#### ucwords

Convert the value with function `ucwords`

```php
    // ...
    'filters' => [
        'ucwords' => true
    ]
    // ...
```

#### uppercase

Convert the value to uppercase

```php
    // ...
    'filters' => [
        'uppercase' => true
    ]
    // ...
```

### Custom Validation Rule

Please follow below steps to create new validation rule

#### Create Rule Handler

Create new class that handle the validation.

```php
<?php

namespace MyModule\Module;

class Validator
{
    public static function custom(
        mixed  $value,
        mixed  $options,
        object $object,
        string $fname,
        array  $rules
    ) {
        // most of the time, the null value is for `required` rule.
        // leave it for it.
        if (is_null($value)) {
            return null;
        }

        if (/* check the $value */) {
            return null; // it's valid
        }

        // its not valid
        return ['100.0'];
        // return ['100.0', ['a'=>'b']];
        // return ['100.0', ['a'=>'b'], 'Error message'];
    }
}

```

The method should return `null` if the value is valid. And array with at least one
array member with max three members. Each members is as below:

1. Index 0. For the error code.
1. Index 1. Translation parameters, forwarded to translation function
1. Index 2. Custom translation key

If index 2 is not exists, default translation from config will be used.

The method will be called with below parameters:

##### mixed $value

The value that user posted. If the value is not posted, it will be `null`. Most
of the time, you don't want to process the `null` value. Leave it to `required`
rule.

##### mixed $options

The rule array value that taken from config. See below as an example:

```php
    // ...
    'rules' => [
        'custom' => 'awesome'
    ]
    // ...
```

The value of this parameter will be `awesome`.

##### object $object

The complete object property that is being validated.

##### string $fname

The field name that is being validated from the `$object` object.

##### array $rules

All rules that going to be applied to the field.

#### Inject Validation Config

This module use [iqomp/config](https://github.com/iqomp/config) for all configs.
Create new file named `iqomp/config/validator.php` under your module main folder.

Fill the file with content as below:

```php
<?php

return [
    // error translation key
    'errors' => [
        '/code/' => '/translation key',
        '100.0' => 'the value is not accepted'
    ],
    'validators' => [
        'custom' => 'MyModule\\Module\\Validator::custom'
    ]
];
```

Update your `composer.json` file to register the new config as below:

```json
    "extra": {
        "iqomp/config": "iqomp/config/"
    },
```

Make sure to call `composer update` after modifing the config.

#### Create Error Translation

This module use [iqomp/locale](https://github.com/iqomp/locale) for the translations.

Create new file named `iqomp/locale/en-US/validator.php` under your module main
folder. Fill the file with content as below:

```php
<?php

return [
    'the value is not accepted' => 'The value is not accepted. Please use other value'
];
```

Update your `composer.json` file to register the new locales as below:

```json
    "extra": {
        "iqomp/locale": "iqomp/locale/"
    },
```

Make sure to call `composer update` after modifing the locales.

### Custom Validation Filter

As of validation rule, filter can be custom as well. Please follow below steps
to create new filter:

#### Create Filter Handler

Create new class that handle the filter.

```php
<?php

namespace MyModule\Module;

class Filter
{
    public static function custom(
        mixed  $value,
        mixed  $options,
        object $object,
        string $fname,
        array  $rules
    ) {
        // most of the time, it's not processed if it's null.
        if (is_null($value)) {
            return null;
        }

        // modify the $value;

        return $value;
    }
}

```

The method will be called exactly like custom rule validation handler called.

#### Inject Validation Config

Create new file named `iqomp/config/validator.php` under your module main folder.

Fill the file with content as below:

```php
<?php

return [
    'filters' => [
        'custom' => 'MyModule\\Module\\Filter::custom'
    ]
];
```

Update your `composer.json` file to register the new config as below:

```json
    "extra": {
        "iqomp/config": "iqomp/config/"
    },
```

Make sure to call `composer update` after modifing the config.

### Custom Error Formatter

By default, error returned by the class is in this below format:

```
stdClass Object
(
    [field] => name
    [code] => 11.0
    [text] => This field is required
    [options] => Array
        (
            [label] => Name
            [rules] => Array
                (
                    [required] => 1
                )

            [name] => name
        )

)
```

You can modify the structure of the error object if you need to. Create a class
that implements the interface `Iqomp\Validator\ErrorFormatterInterface` with
content as below:

```php
<?php

namespace MyModule\Formatter;

use Iqomp\Validator\ErrorFormatterInterface;

class MyErrorFormatter implements ErrorFormatterInterface
{
    public static function format(object $err, string $fld, array $errs, object $obj)
    {
        return [$err->text];
    }
}
```

After that, registry the handler with one of below way:

#### Validator Config

This way will always use your handler for all request and validation.

Create config named `iqomp/config/validator.php`, and fill it with content as below:

```php
<?php

return [
    'formatter' => 'MyModule\Formatter\MyErrorFormatter'
];
```

Update your `composer.json` file to register the new config as below:

```json
    "extra": {
        "iqomp/config": "iqomp/config/"
    },
```

Make sure to call `composer update` after modifing the config.

#### On The Fly

You can set the formatter on the fly by calling `Validator::setErrorFormatter`.
This will work on current request only. You will need to call it for every request.

```php
<?php

use MyModule\Formatter\MyErrorFormatter;
use Iqomp\Validator\Validator;

Validator::setErrorFormatter(MyErrorFormatter::class);
```


## Form

Class `Iqomp\Validator\Form` is a class for separation between rules config and
object to validate. This class make it even simpler on controller to validate
the object by keeping all object validation rules in a config file. If the object
to validate is not provided, it will take from request body.

```php
use Iqomp\Validator\Form;

$form   = new Form('form-name');
$result = $form->validate();

if (!$result) {
    $errors = $form->getErrors();
}
```

### Form Configuration

As before, you need to create list of rules for the form name to be able to use in
in that simple way.

Create new file `iqomp/config/form.php` on your module main directory with content
as below:

```php
<?php

return [
    'forms' => [
        '/form-name/' => [
            '/field/' => [
                'rules' => [
                    // list of rules
                ],
                'filters' => [
                    // list of filters
                ]
            ]
        ]
    ]
];

```

Update your `composer.json` file to register the new config as below:

```json
    "extra": {
        "iqomp/config": "iqomp/config/"
    },
```

Make sure to call `composer update` after modifing the config.

### Method

The class `Iqomp\Validator\Form` has methods as below:

#### __construct(string $name): Form

Create new form object with form `$name`.

#### addError(string $field, string $code, string $text): void

Add custom error to a `$field` with error code `$code` and message `$text`. This
action will not translate the error message from error code, you'll need to do it
on your action.

#### getError(string $field): ?object

Get single error object by `$field` name. The structure is as below:

```
stdClass Object
(
    [field] => name
    [code] => 11.0
    [text] => This field is required
    [options] => Array
        (
            [label] => Name
            [rules] => Array
                (
                    [required] => 1
                )

            [name] => name
        )

)
```

#### getErrors(): array

Get all exists errors with array field->(object)error pair. The structure is as
below:

```
Array
(
    [name] => stdClass Object
        (
            [field] => name
            [code] => 11.0
            [text] => This field is required
            [options] => Array
                (
                    [rules] => Array
                        (
                            [required] => 1
                        )

                    [name] => name
                )

        )

)
```

#### getName(): string

Get current form name.

#### getResult(): ?object

Get final result of validation

#### hasError(): bool

Check if error exists on this form after validation

#### validate(object $object = null): ?object

Validate the `$object` againts the rules based on config. If `$object` is null,
the value will taken from request body.