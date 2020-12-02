<?php
declare(strict_types=1);

namespace Iqomp\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Validator\Filter;

class FilterTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testFilter($method, $value, $expect, $option=null){
        $val = Filter::$method($value, $option, 0, 0, 0);
        $this->assertEquals($expect, $val);
    }

    public function filterProvider() {
        $data = [
            'array:0' => [0, [0]],
            'array:1' => [1, [1]],
            'array:2' => [[], []],
            'array:3' => [(object)[], []],
            'array:4' => [(object)['a'=>1], ['a'=>1]],

            'boolean:0' => [0, false],
            'boolean:1' => [[], false],
            'boolean:2' => [1, true],
            'boolean:3' => [[1], true],
            'boolean:4' => [(object)[], true],

            'float:0' => [0, (float)0],
            'float:1' => [1, (float)1],
            'float:2' => ['12.21', 12.21],

            'integer:0' => [0, 0],
            'integer:1' => [1, 1],
            'integer:2' => [[], 0],
            'integer:3' => ['0', 0],
            'integer:4' => ['12.2', 12],
            'integer:5' => [33.21, 33],

            'lowercase:0' => ['Lorem ipsum', 'lorem ipsum'],
            'lowercase:1' => ['LoremIpsum', 'loremipsum'],
            'lowercase:2' => ['lorem', 'lorem'],

            'object:0' => [[], (object)[]],
            'object:1' => [[1], (object)[1]],
            'object:2' => [1, (object)1],
            'object:3' => [['a'=>'b'], (object)['a'=>'b']],

            'round:0' => [1.2, 1],
            'round:1' => [1.6, 2],
            'round:2' => [1.222, 1.2, 1],
            'round:3' => [1.256, 1.26, 2],

            'string:0' => [1, '1'],
            'string:1' => [0, '0'],
            'string:2' => [12.22, '12.22'],

            'ucwords:0' => ['loremIpsum', 'LoremIpsum'],
            'ucwords:1' => ['lorem ipsum', 'Lorem Ipsum'],

            'uppercase:0' => ['lorem', 'LOREM'],
            'uppercase:1' => ['LoREm', 'LOREM']
        ];

        $result = [];
        foreach($data as $key => $opts){
            $methods = explode(':', $key);
            $method  = $methods[0];

            $result[$key] = [
                $method,
                $opts[0],
                $opts[1],
                $opts[2] ?? true
            ];
        }

        return $result;
    }
}
