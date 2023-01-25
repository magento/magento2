<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Convert\Test\Unit;

use Magento\Framework\Convert\DataSize;
use PHPUnit\Framework\TestCase;

class DataSizeTest extends TestCase
{
    /**
     * @var DataSize
     */
    protected $dataSize;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
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
