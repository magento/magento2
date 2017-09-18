<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit;

use \Magento\Framework\Stdlib\BooleanUtils;

class BooleanUtilsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BooleanUtils
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new BooleanUtils();
    }

    public function testConstructor()
    {
        $object = new BooleanUtils(['yep'], ['nope']);
        $this->assertTrue($object->toBoolean('yep'));
        $this->assertFalse($object->toBoolean('nope'));
    }

    /**
     * @param mixed $input
     * @param bool $expected
     *
     * @dataProvider toBooleanDataProvider
     */
    public function testToBoolean($input, $expected)
    {
        $actual = $this->object->toBoolean($input);
        $this->assertSame($expected, $actual);
    }

    public function toBooleanDataProvider()
    {
        return [
            'boolean "true"' => [true, true],
            'boolean "false"' => [false, false],
            'boolean string "true"' => ['true', true],
            'boolean string "false"' => ['false', false],
            'boolean numeric "1"' => [1, true],
            'boolean numeric "0"' => [0, false],
            'boolean string "1"' => ['1', true],
            'boolean string "0"' => ['0', false]
        ];
    }

    /**
     * @param mixed $input
     *
     * @dataProvider toBooleanExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Boolean value is expected
     */
    public function testToBooleanException($input)
    {
        $this->object->toBoolean($input);
    }

    public function toBooleanExceptionDataProvider()
    {
        return [
            'boolean string "on"' => ['on'],
            'boolean string "off"' => ['off'],
            'boolean string "yes"' => ['yes'],
            'boolean string "no"' => ['no'],
            'boolean string "TRUE"' => ['TRUE'],
            'boolean string "FALSE"' => ['FALSE'],
            'empty string' => [''],
            'null' => [null]
        ];
    }
}
