<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Shell;

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
