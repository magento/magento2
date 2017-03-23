<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Shell\Test\Unit;

use \Magento\Framework\Shell\ComplexParameter;

class ComplexParameterTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFromArray()
    {
        $object = new ComplexParameter('baz');
        $this->assertSame([], $object->getFromArray(['--foo', '--bar']));
        $this->assertSame([], $object->getFromArray(['--foo', '--bar', '--baz']));
        $this->assertSame([1 => ''], $object->getFromArray(['--foo', '--bar', '--baz=1']));
    }

    /**
     * @param string $str
     * @param array $expected
     * @dataProvider getFromStringDataProvider
     */
    public function testGetFromString($str, $expected)
    {
        $object = new ComplexParameter('foo');
        $this->assertSame($expected, $object->getFromString($str));
    }

    /**
     * @return array
     */
    public function getFromStringDataProvider()
    {
        return [
            ['--not-matching', []],
            ['--foo', []],
            ['--foo=', []],
            ['--foo=1', [1 => '']],
            ['--foo=bar=1&baz=2', ['bar' => '1', 'baz' => '2']],
            ['--foo=bar[1]=2&baz[3]=4', ['bar' => [1 => '2'], 'baz' => [3 => '4']]],
            ['--foo=bar[one]=value1&bar[two]=value2', ['bar' => ['one' => 'value1', 'two' => 'value2']]],
        ];
    }

    public function testPattern()
    {
        $object = new ComplexParameter('f', '/^-%s=(bar|baz)$/');
        $this->assertSame([], $object->getFromString('-f=1'));
        $this->assertSame(['bar' => ''], $object->getFromString('-f=bar'));
        $this->assertSame(['baz' => ''], $object->getFromString('-f=baz'));
    }

    public function testMergeFromArgv()
    {
        $object = new ComplexParameter('foo');
        $server = ['argv' => ['--foo=bar=value1', '--nonfoo=value2']];
        $into = ['baz' => 'value3'];
        $this->assertSame(['baz' => 'value3', 'bar' => 'value1'], $object->mergeFromArgv($server, $into));
    }
}
