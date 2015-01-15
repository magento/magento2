<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache\Type;


class ConfigSegmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @param array $expected
     * @dataProvider getDataDataProvider
     */
    public function testGetData($data, $expected)
    {
        $object = new ConfigSegment($data);
        $this->assertSame($expected, $object->getData());
    }

    /**
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            [[], []],
            [['FoO' => '1'], ['FoO' => 1]],
            [['foo' => false, 'bar' => true], ['foo' => 0, 'bar' => 1]],
            [['foo' => 'bar', 'baz' => '0'], ['foo' => 0, 'baz' => 0]],
            [['foo' => []], ['foo' => 0]],
            [['foo' => [0]], ['foo' => 1]],
            [['foo' => [1, 2]], ['foo' => 1]],
        ];
    }

    /**
     * @param array $data
     * @dataProvider getDataInvalidKeysDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid cache type key:
     */
    public function testGetDataInvalidKeys($data)
    {
        new ConfigSegment($data);
    }

    /**
     * @return array
     */
    public function getDataInvalidKeysDataProvider()
    {
        return [
            [[1]],
            [['0' => 1]],
            [['in/valid' => 1]],
        ];
    }
}
