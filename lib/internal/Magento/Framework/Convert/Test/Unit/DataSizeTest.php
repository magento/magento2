<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Convert\Test\Unit;

use Magento\Framework\Convert\DataSize;

/**
 * Class DataSizeTest
 */
class DataSizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Convert\DataSize
     */
    protected $dataSize;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->dataSize = new DataSize();
    }

    /**
     * @dataProvider getConvertSizeToIntegerDataProvider
     * @backupStaticAttributes
     * @param string $value
     * @param int $expected
     * @return void
     */
    public function testConvertSizeToInteger($value, $expected)
    {
        $this->assertEquals($expected, $this->dataSize->convertSizeToBytes($value));
    }

    /**
     * @return array
     */
    public function getConvertSizeToIntegerDataProvider()
    {
        return [
            ['0K', 0],
            ['123K', 125952],
            ['1K', 1024],
            ['1g', 1073741824],
            ['asdas', 0],
            ['1M', 1048576]
        ];
    }
}
