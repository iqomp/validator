<?php
declare(strict_types=1);

namespace Iqomp\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Validator\Validator;
use Iqomp\Validator\RuleNotRegisteredException;
use Iqomp\Validator\FilterNotRegisteredException;
use Iqomp\Validator\ErrorFormatterInterface;

class CustomErrorFormatter implements ErrorFormatterInterface
{
    public static function format(object $err, string $fld, array $errs, object $obj) {
        return [$err->text];
    }
}

class ValidatorTest extends TestCase
{
    public function testResult()
    {
        $object = (object)['name' => 'User'];
        $rules  = [
            'name' => [
                'rules' => [
                    'required' => true
                ]
            ]
        ];

        list($result, $error) = Validator::validate($rules, $object);

        $this->assertEquals($object, $result);
    }

    public function testIgnoreUnruledProperty()
    {
        $object = (object)['name' => 'User', 'email' => 'email@host.com'];
        $rules  = [
            'name' => [
                'rules' => [
                    'required' => true
                ]
            ]
        ];
        $expect = (object)['name' => 'User'];

        list($result, $error) = Validator::validate($rules, $object);

        $this->assertEquals($expect, $result);
    }

    public function testErrorOnSuccess()
    {
        $object = (object)['name' => 'User'];
        $rules  = [
            'name' => [
                'rules' => [
                    'required' => true
                ]
            ]
        ];

        list($result, $error) = Validator::validate($rules, $object);

        $this->assertEquals([], $error);
    }

    public function testError()
    {
        $object = (object)['name' => 'User'];
        $rules  = [
            'name' => [
                'rules' => [
                    'required' => true,
                    'numeric' => true
                ]
            ]
        ];

        list($result, $error) = Validator::validate($rules, $object);

        $this->assertArrayHasKey('name', $error);
    }

    public function testRuleNotDefined()
    {
        $object = (object)['name' => 'User'];
        $rules  = [
            'name' => [
                'rules' => [
                    'required' => true,
                    'non-exists' => true
                ]
            ]
        ];

        $this->expectException(RuleNotRegisteredException::class);
        list($result, $error) = Validator::validate($rules, $object);
    }

    public function testArrayItem()
    {
        $object = (object)[
            'tags' => ['one', ['two'], 'three']
        ];
        $rules = [
            'tags' => [
                'rules' => [
                    'array' => true,
                ],
                'children' => [
                    '*' => [
                        'rules' => [
                            'text' => true
                        ]
                    ]
                ]
            ]
        ];

        list($result, $error) = Validator::validate($rules, $object);
        $this->assertArrayHasKey('tags.1', $error);
    }

    public function testObjectProperty()
    {
        $object = (object)[
            'from' => (object)[
                'user' => (object)[
                    'id' => 1
                ]
            ]
        ];
        $rules = [
            'from' => [
                'rules' => [
                    'object' => true
                ],
                'children' => [
                    'user' => [
                        'rules' => [
                            'object' => true
                        ],
                        'children' => [
                            'id' => [
                                'rules' => [
                                    'object' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        list($result, $error) = Validator::validate($rules, $object);

        $this->assertArrayHasKey('from.user.id', $error);
    }

    public function testFilter()
    {
        $object = (object)['age' => '12'];
        $rules = [
            'age' => [
                'rules' => [
                    'numeric' => true
                ],
                'filters' => [
                    'integer' => true
                ]
            ]
        ];

        list($result, $error) = Validator::validate($rules, $object);

        $this->assertIsInt($result->age);
    }

    public function testFilterNotFound()
    {
        $object = (object)['age' => '12'];
        $rules = [
            'age' => [
                'rules' => [
                    'numeric' => true
                ],
                'filters' => [
                    'not-found' => true
                ]
            ]
        ];

        $this->expectException(FilterNotRegisteredException::class);
        list($result, $error) = Validator::validate($rules, $object);
    }

    public function testCustomErrorFormatter()
    {
        $object = (object)['name' => 'User'];
        $rules  = [
            'name' => [
                'rules' => [
                    'required' => true,
                    'numeric' => true
                ]
            ]
        ];

        Validator::setErrorFormatter(CustomErrorFormatter::class);

        list($result, $error) = Validator::validate($rules, $object);

        $expected = ['The valus is not numeric'];

        $this->assertEquals($expected, $error['name']);
    }
}
