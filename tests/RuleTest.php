<?php
declare(strict_types=1);

namespace Iqomp\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Validator\Rule;

class Callback {
    static function action($val){
        return $val;
    }
}

final class RuleTest extends TestCase
{
    /**
     * @dataProvider ruleProvider
     */
    public function testRule($method, $expect, $value, $options=null, $object=null, $rules=null, $upload=null){
        if(!$object)
            $object = new \stdClass();

        if($upload){
            foreach($upload as $name => $val)
                $_FILES[$name] = $val;
        }

        $object->$method = $value;

        $result = Rule::$method($value, $options, $object, $method, $rules);
        if(is_array($result))
            $result = $result[0];

        $this->assertEquals($expect, $result);
    }

    public function ruleProvider(){
        $data = [
            'array:true/0' => [
                'value' => []
            ],
            'array:true/1' => [
                'value' => null
            ],
            'array:true/2' => [
                'value' => ''
            ],
            'array:true/3' => [
                'value' => [1,2]
            ],
            'array:true/4' => [
                'value' => ['a'=>'b']
            ],
            'array:true/5' => [
                'value' => true,
                'expect' => '1.0'
            ],
            'array:true/6' => [
                'value' => (object)[],
                'expect' => '1.0'
            ],

            'array:indexed/0' => [
                'value' => [],
                'option' => 'indexed'
            ],
            'array:indexed/1' => [
                'value' => [1,2],
                'option' => 'indexed'
            ],
            'array:indexed/2' => [
                'value' => ['a'=>'b'],
                'option' => 'indexed',
                'expect' => '1.1'
            ],

            'array:assoc/0' => [
                'value' => ['a'=>'b'],
                'option' => 'assoc'
            ],
            'array:assoc/1' => [
                'value' => [1,2],
                'option' => 'assoc',
                'expect' => '1.2'
            ],

            'callback:action/0' => [
                'value' => null,
                'option' => 'Iqomp\Tests\Callback::action'
            ],
            'callback:action/1' => [
                'value' => ['100.1'],
                'option' => 'Iqomp\Tests\Callback::action',
                'expect' => '100.1'
            ],

            'date:Y-m-d/0' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d']
            ],
            'date:Y-m-d/1' => [
                'value' => '',
                'option' => ['format' => 'Y-m-d']
            ],
            'date:Y-m-d/2' => [
                'value' => date('Y-m-d H'),
                'option' => ['format' => 'Y-m-d H']
            ],
            'date:Y-m-d/3' => [
                'value'  => 'not a date',
                'option' => ['format' => 'Y-m-d'],
                'expect' => '2.0'
            ],

            'date:Y-m-d/min/0' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min' => '-1 days']
            ],
            'date:Y-m-d/min/1' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min' => '1945-01-01']
            ],
            'date:Y-m-d/min/2' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min' => '+1 days'],
                'expect' => '2.1'
            ],
            'date:Y-m-d/min/2' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min' => date('Y-m-d', strtotime('+1 days'))],
                'expect' => '2.1'
            ],

            'date:Y-m-d/max/0' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'max' => '+1 days']
            ],
            'date:Y-m-d/max/1' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'max' => '2120-01-01']
            ],
            'date:Y-m-d/max/2' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'max' => '-1 days'],
                'expect' => '2.2'
            ],
            'date:Y-m-d/max/2' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'max' => date('Y-m-d', strtotime('-1 days'))],
                'expect' => '2.2'
            ],

            'date:Y-m-d/min-max/0' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min' => '-1 days', 'max' => '+1 days']
            ],
            'date:Y-m-d/min-max/1' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min' => '1945-01-01', 'max' => '2120-01-01']
            ],
            'date:Y-m-d/min-max/2' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min' => '-10 days', 'max' => '-1 days'],
                'expect' => '2.2'
            ],

            'date:Y-m-d/min-field/0' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min-field' => 'min-date'],
                'object' => (object)['min-date' => date('Y-m-d', strtotime('-1 days'))]
            ],
            'date:Y-m-d/min-field/1' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min-field' => 'min-date'],
                'object' => (object)['min-date' => '1945-01-01']
            ],
            'date:Y-m-d/min-field/2' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'min-field' => 'min-date'],
                'expect' => '2.1',
                'object' => (object)['min-date' => '2120-01-01']
            ],

            'date:Y-m-d/max-field/0' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'max-field' => 'max-date'],
                'object' => (object)['max-date' => date('Y-m-d', strtotime('+1 days'))]
            ],
            'date:Y-m-d/max-field/1' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'max-field' => 'max-date'],
                'object' => (object)['max-date' => '2120-01-01']
            ],
            'date:Y-m-d/max-field/2' => [
                'value' => date('Y-m-d'),
                'option' => ['format' => 'Y-m-d', 'max-field' => 'max-date'],
                'expect' => '2.2',
                'object' => (object)['max-date' => '1945-01-01']
            ],

            'email:true/0' => [
                'value' => 'name@host.com'
            ],
            'email:true/1' => [
                'value' => ''
            ],
            'email:true/2' => [
                'value' => null
            ],
            'email:true/3' => [
                'value' => 'not email',
                'expect' => '3.0'
            ],

            'empty:false/0' => [
                'value' => 'lorem',
                'option' => false
            ],
            'empty:false/1' => [
                'value' => null,
                'option' => false
            ],
            'empty:false/2' => [
                'value' => 0,
                'expect' => '21.0',
                'option' => false
            ],
            'empty:false/3' => [
                'value' => false,
                'expect' => '21.0',
                'option' => false
            ],
            'empty:false/4' => [
                'value' => '',
                'expect' => '21.0',
                'option' => false
            ],

            'empty:true/0' => [
                'value' => '',
                'option' => true
            ],
            'empty:true/1' => [
                'value' => null,
                'option' => true
            ],
            'empty:true/2' => [
                'value' => 0,
                'option' => true
            ],
            'empty:true/3' => [
                'value' => false,
                'option' => true
            ],
            'empty:true/4' => [
                'value' => 'nah',
                'expect' => '21.1',
                'option' => true
            ],

            'equalsTo:[]/0' => [
                'value' => 'val',
                'option' => 'reff-field',
                'object' => (object)['reff-field'=>'val']
            ],
            'equalsTo:[]/1' => [
                'value' => 'val',
                'option' => 'reff-field',
                'expect' => '26.1'
            ],
            'equalsTo:[]/2' => [
                'value' => 'val',
                'option' => 'reff-field',
                'expect' => '26.1',
                'object' => (object)['reff-field'=>'val2']
            ],

            'file:[]/0' => [
                'value' => [
                    'name' => 'facepalm.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => '/tmp/phpn3FmFr',
                    'error' => 0,
                    'size' => 15476
                ],
                'upload' => [
                    'file' => [
                        'name' => 'facepalm.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => '/tmp/phpn3FmFr',
                        'error' => 0,
                        'size' => 15476
                    ]
                ]
            ],
            'file:[]/1' => [
                'value' => null,
                'expect' => '28.0',
                'upload' => [
                    'file' => [
                        'name' => 'facepalm.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => '/tmp/phpn3FmFr',
                        'error' => 0,
                        'size' => 15476
                    ]
                ]
            ],
            'file:[]/2' => [
                'value' => 'not file upload',
                'expect' => '28.0',
                'upload' => [
                    'file' => [
                        'name' => 'facepalm.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => '/tmp/phpn3FmFr',
                        'error' => 0,
                        'size' => 15476
                    ]
                ]
            ],

            'in:[]/0' => [
                'value' => 'one',
                'option' => ['one','two','three']
            ],
            'in:[]/1' => [
                'value' => '',
                'option' => ['one','two','three']
            ],
            'in:[]/2' => [
                'value' => null,
                'option' => ['one','two','three']
            ],
            'in:[]/3' => [
                'value' => 'one',
                'option' => ['#one' => 'one', '#two' => 'two']
            ],
            'in:[]/4' => [
                'value' => 'four',
                'expect' => '4.0',
                'option' => ['one','two','three']
            ],

            'ip:true/0' => [
                'value' => '127.0.0.1'
            ],
            'ip:true/1' => [
                'value' => ''
            ],
            'ip:true/2' => [
                'value' => '2001:db8:0:0:1::1'
            ],
            'ip:true/3' => [
                'value' => '127.0.0.1.0',
                'expect' => '5.0'
            ],

            'ip:4/0' => [
                'value' => '127.0.0.1',
                'option' => 4
            ],
            'ip:4/1' => [
                'value' => '2001:db8:0:0:1::1',
                'expect' => '5.1',
                'option' => 4
            ],
            'ip:4/2' => [
                'value' => '127.0.0.1.0',
                'expect' => '5.1',
                'option' => 4
            ],

            'ip:6/0' => [
                'value' => '2001:db8:0:0:1::1',
                'option' => 6
            ],
            'ip:6/1' => [
                'value' => '127.0.0.1',
                'expect' => '5.2',
                'option' => 6
            ],
            'ip:6/2' => [
                'value' => '127.0.0.1.0',
                'expect' => '5.2',
                'option' => 6
            ],

            'json:true/0' => [
                'value' => '{"json":"object"}'
            ],
            'json:true/1' => [
                'value' => ''
            ],
            'json:true/2' => [
                'value' => '["json","array"]'
            ],
            'json:true/3' => [
                'value' => '"json string"'
            ],
            'json:true/4' => [
                'value' => '{"not":"valid","json"}',
                'expect' => '23.1'
            ],

            'length:min/0' => [
                'value' => 'one',
                'option' => ['min' => 1]
            ],
            'length:min/1' => [
                'value' => ['one','two'],
                'option' => ['min' => 1]
            ],
            'length:min/2' => [
                'value' => ['one'],
                'expect' => '6.0',
                'option' => ['min' => 2]
            ],
            'length:min/3' => [
                'value' => 'one',
                'expect' => '6.0',
                'option' => ['min' => 4]
            ],

            'length:max/0' => [
                'value' => 'one',
                'option' => ['max' => 4]
            ],
            'length:max/1' => [
                'value' => ['one','two'],
                'option' => ['max' => 2]
            ],
            'length:max/2' => [
                'value' => ['one','two'],
                'expect' => '6.1',
                'option' => ['max' => 1]
            ],
            'length:max/3' => [
                'value' => 'one',
                'expect' => '6.1',
                'option' => ['max' => 2]
            ],

            'notin:[]/0' => [
                'value' => 'one',
                'option' => ['two','three']
            ],
            'notin:[]/1' => [
                'value' => '',
                'option' => ['two','three']
            ],
            'notin:[]/2' => [
                'value' => null,
                'option' => ['two','three']
            ],
            'notin:[]/3' => [
                'value' => 'two',
                'expect' => '7.0',
                'option' => ['two','three']
            ],
            'notin:[]/4' => [
                'value' => 'two',
                'expect' => '7.0',
                'option' => ['#two'=>'two','#three'=>'three']
            ],

            'numeric:true/0' => [
                'value' => '12'
            ],
            'numeric:true/1' => [
                'value' => '12.22'
            ],
            'numeric:true/2' => [
                'value' => '12x22',
                'expect' => '8.0'
            ],

            'numeric:min/0' => [
                'value' => '12',
                'option' => ['min'=>11]
            ],
            'numeric:min/1' => [
                'value' => '12',
                'expect' => '8.1',
                'option' => ['min'=>13]
            ],

            'numeric:max/0' => [
                'value' => '12',
                'option' => ['max'=>12]
            ],
            'numeric:max/1' => [
                'value' => '12',
                'expect' => '8.2',
                'option' => ['max'=>11]
            ],

            // 'numeric:decimal/0' => [
            //     'value' => '12.12',
            //     'option' => ['decimal'=>2]
            // ],

            'object:true/0' => [
                'value' => (object)['a'=>'b']
            ],
            'object:true/1' => [
                'value' => null
            ],
            'object:true/2' => [
                'value' => ['array'],
                'expect' => '9.0'
            ],
            'object:true/3' => [
                'value' => 'string',
                'expect' => '9.0'
            ],

            'regex:str/0' => [
                'value' => 'a',
                'option' => '!^.$!'
            ],
            'regex:str/1' => [
                'value' => '12a1',
                'option' => '!^[0-9]{2}[a-z][0-9]$!'
            ],
            'regex:str/2' => [
                'value' => null,
                'option' => '!^[0-9]{2}[a-z][0-9]$!'
            ],
            'regex:str/3' => [
                'value' => '',
                'option' => '!^[0-9]{2}[a-z][0-9]$!'
            ],
            'regex:str/4' => [
                'value' => '12',
                'expect' => '10.0',
                'option' => '!^[a-z]$!'
            ],

            'required:true/0' => [
                'value' => 'null'
            ],
            'required:true/1' => [
                'value' => ''
            ],
            'required:true/2' => [
                'value' => null,
                'expect' => '11.0'
            ],

            'requiredOn:=/0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)['cd' =>'cd']
            ],
            'requiredOn:=/1' => [
                'value' => null,
                'option' => ['cd' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)['cd' =>'ef']
            ],
            'requiredOn:=/2' => [
                'value' => 'ab',
                'option' => ['cd.ef.gh' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)[
                    'cd' => (object)[
                        'ef' => (object)[
                            'gh' => 'cd'
                        ]
                    ]
                ]
            ],
            'requiredOn:=/3' => [
                'value' => 'ab',
                'option' => ['cd.0.gh' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)[
                    'cd' => [
                        (object)[
                            'gh' => 'cd'
                        ]
                    ]
                ]
            ],
            'requiredOn:=/4' => [
                'value' => null,
                'option' => ['cd.0.fg' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)[
                    'cd' => [
                        (object)[
                            'gh' => 'cd'
                        ]
                    ]
                ]
            ],
            'requiredOn:=/5' => [
                'value' => null,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)['cd' =>'cd']
            ],
            'requiredOn:=/7' => [
                'value' => null,
                'expect' => '11.0',
                'option' => ['cd.ef.gh' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)[
                    'cd' => (object)[
                        'ef' => (object)[
                            'gh' => 'cd'
                        ]
                    ]
                ]
            ],
            'requiredOn:=/8' => [
                'value' => null,
                'expect' => '11.0',
                'option' => ['cd.0.gh' => ['operator'=>'=','expected'=>'cd']],
                'object' => (object)[
                    'cd' => [
                        (object)[
                            'gh' => 'cd'
                        ]
                    ]
                ]
            ],

            'requiredOn:!=/0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'!=','expected'=>'cd']],
                'object' => (object)['cd' =>'cd']
            ],
            'requiredOn:!=/1' => [
                'value' => NULL,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'!=','expected'=>'cd']],
                'object' => (object)['cd' =>'ef']
            ],

            'requiredOn:>/0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'>','expected'=>1]],
                'object' => (object)['cd' =>2]
            ],
            'requiredOn:>/1' => [
                'value' => NULL,
                'option' => ['cd' => ['operator'=>'>','expected'=>1]],
                'object' => (object)['cd' =>1]
            ],
            'requiredOn:>/2' => [
                'value' => NULL,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'>','expected'=>1]],
                'object' => (object)['cd' =>2]
            ],

            'requiredOn:</0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'<','expected'=>2]],
                'object' => (object)['cd' =>1]
            ],
            'requiredOn:</1' => [
                'value' => NULL,
                'option' => ['cd' => ['operator'=>'<','expected'=>2]],
                'object' => (object)['cd' =>2]
            ],
            'requiredOn:</2' => [
                'value' => NULL,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'<','expected'=>2]],
                'object' => (object)['cd'=>1]
            ],

            'requiredOn:>=/0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'>=','expected'=>1]],
                'object' => (object)['cd' =>2]
            ],
            'requiredOn:>=/1' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'>=','expected'=>2]],
                'object' => (object)['cd' =>2]
            ],
            'requiredOn:>=/2' => [
                'value' => NULL,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'>=','expected'=>2]],
                'object' => (object)['cd'=>2]
            ],
            'requiredOn:>=/3' => [
                'value' => NULL,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'>=','expected'=>2]],
                'object' => (object)['cd'=>3]
            ],

            'requiredOn:<=/0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'<=','expected'=>2]],
                'object' => (object)['cd' =>1]
            ],
            'requiredOn:<=/1' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'<=','expected'=>2]],
                'object' => (object)['cd' =>2]
            ],
            'requiredOn:<=/2' => [
                'value' => NULL,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'<=','expected'=>2]],
                'object' => (object)['cd'=>2]
            ],
            'requiredOn:<=/3' => [
                'value' => NULL,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'<=','expected'=>2]],
                'object' => (object)['cd'=>1]
            ],

            'requiredOn:in/0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'in','expected'=>['one','two']]],
                'object' => (object)['cd' =>'one']
            ],
            'requiredOn:in/1' => [
                'value' => null,
                'option' => ['cd' => ['operator'=>'in','expected'=>['one','two']]],
                'object' => (object)['cd' =>'three']
            ],
            'requiredOn:in/2' => [
                'value' => null,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'in','expected'=>['one','two']]],
                'object' => (object)['cd' =>'one']
            ],

            'requiredOn:!in/0' => [
                'value' => 'ab',
                'option' => ['cd' => ['operator'=>'!in','expected'=>['one','two']]],
                'object' => (object)['cd' =>'three']
            ],
            'requiredOn:!in/1' => [
                'value' => null,
                'option' => ['cd' => ['operator'=>'!in','expected'=>['one','two']]],
                'object' => (object)['cd' =>'one']
            ],
            'requiredOn:!in/2' => [
                'value' => null,
                'expect' => '11.0',
                'option' => ['cd' => ['operator'=>'!in','expected'=>['one','two']]],
                'object' => (object)['cd' =>'three']
            ],

            'text:true/0' => [
                'value' => '',
            ],
            'text:true/1' => [
                'value' => null,
            ],
            'text:true/2' => [
                'value' => 'random text',
            ],
            'text:true/3' => [
                'value' => 12,
                'expect' => '12.0'
            ],
            'text:true/3' => [
                'value' => (object)[],
                'expect' => '12.0'
            ],

            'text:slug/0' => [
                'value' => 'some-slug',
                'option' => 'slug'
            ],
            'text:true/1' => [
                'value' => 'Not a slug',
                'expect' => '12.1',
                'option' => 'slug'
            ],

            'text:alnumdash/0' => [
                'value' => 'S0m3-all-NUM-and-Dash',
                'option' => 'alnumdash'
            ],
            'text:alnumdash/1' => [
                'value' => '123-1-230123-12-3',
                'option' => 'alnumdash'
            ],
            'text:alnumdash/2' => [
                'value' => 'Some-Space and all-NUM-and-Dash',
                'expect' => '12.2',
                'option' => 'alnumdash'
            ],

            'text:alpha/0' => [
                'value' => 'SomeAlpha',
                'option' => 'alpha'
            ],
            'text:alpha/1' => [
                'value' => 'SomeAlpha With Space',
                'expect' => '12.3',
                'option' => 'alpha'
            ],
            'text:alpha/2' => [
                'value' => 'SomeAlphaWithNumb3r',
                'expect' => '12.3',
                'option' => 'alpha'
            ],

            'text:alnum/0' => [
                'value' => 'SomeAlpha',
                'option' => 'alnum'
            ],
            'text:alnum/1' => [
                'value' => 'SomeAlphaWithNumb3r',
                'option' => 'alnum'
            ],
            'text:alnum/2' => [
                'value' => 'SomeAlpha With Space',
                'expect' => '12.4',
                'option' => 'alnum'
            ],

            'url:true/0' => [
                'value' => 'http://google.com/'
            ],
            'url:true/1' => [
                'value' => 'http://google.com'
            ],
            'url:true/2' => [
                'value' => ''
            ],
            'url:true/3' => [
                'value' => null
            ],
            'url:true/4' => [
                'value' => 'google.com',
                'expect' => '13.0'
            ],

            'url:path/0' => [
                'value' => 'http://google.com/search',
                'option' => ['path' => true]
            ],
            'url:path/1' => [
                'value' => 'http://google.com',
                'option' => ['path' => true],
                'expect' => '13.1'
            ],

            'url:query/0' => [
                'value' => 'http://google.com/search?q=abc',
                'option' => ['query' => true]
            ],
            'url:query/1' => [
                'value' => 'http://google.com?q=abc',
                'option' => ['query' => ['q']]
            ],
            'url:query/2' => [
                'value' => 'http://google.com',
                'option' => ['query' => ['q']],
                'expect' => '13.2'
            ],
            'url:query/3' => [
                'value' => 'http://google.com?q=abc',
                'option' => ['query' => ['q','page']],
                'expect' => '13.3'
            ],
        ];

        $result = [];
        foreach($data as $key => $opts){
            $methods = explode(':', $key);
            $method  = $methods[0];
            $result[ $key ] = [
                $method,
                $opts['expect'] ?? null,
                $opts['value']  ?? null,
                $opts['option'] ?? true,
                $opts['object'] ?? (object)[],
                $opts['rules']  ?? [],
                $opts['upload'] ?? []
            ];
        }

        return $result;
    }
}
