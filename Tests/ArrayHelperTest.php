<?php

/*
 * This file is part of the Panda Helpers Package.
 *
 * (c) Ioannis Papikas <papikas.ioan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Panda\Support\Helpers;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayHelperTest
 * @package Panda\Support\Helpers
 */
class ArrayHelperTest extends TestCase
{
    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::get
     */
    public function testGet()
    {
        // Plain getter
        $this->assertEquals('test', ArrayHelper::get(['test' => 'test'], 'test'));
        $this->assertEquals('test', ArrayHelper::get(['test1' => 'test'], 'test1'));

        // Getter with no key
        $array = ['test1' => 'test'];
        $this->assertEquals($array, ArrayHelper::get($array));

        // Default
        $this->assertEquals('test', ArrayHelper::get(['test1' => 'test'], 'test1', 'not_exists'));
        $this->assertEquals('not_exists', ArrayHelper::get(['test1' => 'test'], 'test2', 'not_exists'));

        // Dot syntax
        $array = [
            'arr1' => [
                'arr1-1' => 'val1-1',
                'arr1-2' => 'val1-2',
            ],
            'arr2' => [
                'arr2-1' => 'val2-1',
                'arr2-2' => 'val2-2',
            ],
            'arr3.arr3-1' => 'val3-1',
        ];
        $this->assertEquals('val1-1', ArrayHelper::get($array, 'arr1.arr1-1', 'not_exists', true));
        $this->assertEquals('val1-2', ArrayHelper::get($array, 'arr1.arr1-2', 'not_exists', true));
        $this->assertEquals('val2-1', ArrayHelper::get($array, 'arr2.arr2-1', 'not_exists', true));
        $this->assertEquals('val3-1', ArrayHelper::get($array, 'arr3.arr3-1', 'not_exists', true));
        $this->assertEquals('not_exists', ArrayHelper::get($array, 'arr2.arr2-3', 'not_exists', true));
        $this->assertEquals('not_exists', ArrayHelper::get($array, 'arr4.arr4-1', 'not_exists', true));

        // Dot syntax getter (depth = 3, no depth 2 exists)
        $this->assertEquals('not_exists', ArrayHelper::get($array, 'arr1.arr2-1.arr3', 'not_exists', true));
    }

    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::set
     *
     * @throws InvalidArgumentException
     */
    public function testSet()
    {
        $array = [
            't1' => [
                't2' => [
                    't3' => [],
                ],
            ],
        ];

        // Simple assignment
        $array = ArrayHelper::set($array, 't11', 'test_value', false);
        $this->assertEquals('test_value', $array['t11']);

        // Simple assignment using dot, without dot syntax
        $array = ArrayHelper::set($array, 't2.t3', 'test_value', false);
        $this->assertEquals('test_value', $array['t2.t3']);

        // Numeric assignments
        $array = ArrayHelper::set($array, 1, 'test_value', false);
        $this->assertEquals('test_value', $array[1]);
        $array = ArrayHelper::set($array, '2', 'test_value', false);
        $this->assertEquals('test_value', $array['2']);
        $array = ArrayHelper::set($array, '3', 'test_value', false);
        $this->assertEquals('test_value', $array[3]);
        $array = ArrayHelper::set($array, 0, 'test_value', false);
        $this->assertEquals('test_value', $array[0]);

        // Simple assignment, using dot syntax
        $array = ArrayHelper::set($array, 't11', 'test_value', true);
        $this->assertEquals('test_value', $array['t11']);

        // Dot syntax assignment (depth = 2)
        $array = ArrayHelper::set($array, 't1.t22', 'test_value', true);
        $this->assertEquals('test_value', $array['t1']['t22']);

        // Dot syntax assignment (depth = 3)
        $array = ArrayHelper::set($array, 't1.t2.t33', 'test_value', true);
        $this->assertEquals('test_value', $array['t1']['t2']['t33']);

        // Dot syntax assignment (depth = 4)
        $array = ArrayHelper::set($array, 't1.t2.t3.t4', 'test_value', true);
        $this->assertEquals('test_value', $array['t1']['t2']['t3']['t4']);

        // Dot syntax assignment (depth = 2, no depth 1 exists)
        $array = ArrayHelper::set($array, 't3.t4', 'test_value', true);
        $this->assertEquals('test_value', $array['t3']['t4']);
    }

    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::exists
     */
    public function testExists()
    {
        $array = [
            'arr1' => [
                'arr1-1' => 'val1-1',
                'arr1-2' => 'val1-2',
            ],
            'arr2' => [
                'arr2-1' => 'val2-1',
                'arr2-2' => 'val2-2',
            ],
            'arr3.arr3-1' => 'val3-1',
        ];
        $this->assertTrue(ArrayHelper::exists($array, 'arr1', true));
        $this->assertTrue(ArrayHelper::exists($array, 'arr1.arr1-1', true));
        $this->assertTrue(ArrayHelper::exists($array, 'arr3.arr3-1', false));
        $this->assertTrue(ArrayHelper::exists($array, 'arr3.arr3-1', true));

        $this->assertFalse(ArrayHelper::exists($array, 'arr4', true));
        $this->assertFalse(ArrayHelper::exists($array, 'arr1.arr1-3', true));
        $this->assertFalse(ArrayHelper::exists($array, 'arr1.arr1-1.arr11-1', true));
    }

    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::filter
     */
    public function testFilter()
    {
        $array = [
            't11' => 'v11',
            't12' => 'v12',
            't13' => 'v13',
            't14' => 'v14',
            't21' => 'v21',
            't22' => 'v22',
        ];

        // Empty callback
        $result = ArrayHelper::filter($array, null, null);
        $this->assertEquals($array, $result);

        // Empty array --> default
        $result = ArrayHelper::filter([], null, 'default_value');
        $this->assertEquals('default_value', $result);

        // Filter function with some matches
        $result = ArrayHelper::filter($array, [self::class, 'filterCallback1'], 'default_value');
        $this->assertEquals([
            't11' => 'v11',
            't12' => 'v12',
            't13' => 'v13',
            't14' => 'v14',
        ], $result);

        // Filter function with some matches and length
        $result = ArrayHelper::filter($array, [self::class, 'filterCallback1'], 'default_value', 2);
        $this->assertEquals([
            't11' => 'v11',
            't12' => 'v12',
        ], $result);
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function filterCallback1($key)
    {
        if (substr($key, 0, 2) == 't1') {
            return true;
        }

        return false;
    }

    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::merge
     */
    public function testMerge()
    {
        $helper1 = [
            'h11' => 'v11',
            'h12' => 'v12',
        ];
        $array1 = [
            't11' => 'v11',
            't12' => 'v12',
            't3' => $helper1,
        ];
        $helper2 = [
            'h21' => 'v21',
            'h22' => 'v22',
        ];
        $array2 = [
            't21' => 'v21',
            't22' => 'v22',
            't3' => $helper2,
        ];

        // Merge (not deep)
        $result = ArrayHelper::merge($array1, $array2, false);
        $this->assertEquals('v11', $result['t11']);
        $this->assertEquals('v12', $result['t12']);
        $this->assertEquals('v21', $result['t21']);
        $this->assertEquals('v22', $result['t22']);
        $this->assertNotEquals($helper1, $result['t3']);
        $this->assertEquals($helper2, $result['t3']);

        // Merge deep
        $result = ArrayHelper::merge($array1, $array2, true);
        $this->assertEquals('v11', $result['t11']);
        $this->assertEquals('v12', $result['t12']);
        $this->assertEquals('v21', $result['t21']);
        $this->assertEquals('v22', $result['t22']);
        $this->assertEquals('v11', $result['t3']['h11']);
        $this->assertEquals('v12', $result['t3']['h12']);
        $this->assertEquals('v21', $result['t3']['h21']);
        $this->assertEquals('v22', $result['t3']['h22']);
    }

    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::toKeyIndex
     */
    public function testToKeyIndex()
    {
        $array = [
            [
                't1' => 1,
                't2' => 2,
                't3' => 3,
            ],
            [
                't1' => 11,
                't2' => 22,
                't3' => 33,
            ],
            [
                't1' => 111,
                't2' => 222,
                't3' => 333,
            ],
        ];

        $result = ArrayHelper::toKeyIndex($array, 't1');
        $this->assertEquals([
            1 => [
                't1' => 1,
                't2' => 2,
                't3' => 3,
            ],
            11 => [
                't1' => 11,
                't2' => 22,
                't3' => 33,
            ],
            111 => [
                't1' => 111,
                't2' => 222,
                't3' => 333,
            ],
        ], $result);
    }

    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::toKeyValue
     */
    public function testToKeyValue()
    {
        $array = [
            [
                't1' => 1,
                't2' => 2,
                't3' => 3,
            ],
            [
                't1' => 11,
                't2' => 22,
                't3' => 33,
            ],
            [
                't1' => 111,
                't2' => 222,
                't3' => 333,
            ],
        ];

        $result = ArrayHelper::toKeyValue($array, 't1', 't2');
        $this->assertEquals([
            1 => 2,
            11 => 22,
            111 => 222,
        ], $result);
    }

    /**
     * @covers \Panda\Support\Helpers\ArrayHelper::toKeyValueGroup
     */
    public function testToKeyValueGroup()
    {
        $array = [
            [
                't1' => 'v1',
                't2' => 1,
            ],
            [
                't1' => 'v1',
                't2' => 2,
            ],
            [
                't1' => 'v3',
                't2' => 3,
            ],
        ];

        $result = ArrayHelper::toKeyValueGroup($array, 't1', 't2');
        $this->assertEquals([
            'v1' => [1, 2],
            'v3' => [3],
        ], $result);
    }
}
