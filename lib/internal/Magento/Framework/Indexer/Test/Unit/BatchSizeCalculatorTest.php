<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\Indexer\BatchSizeCalculator;

class BatchSizeCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BatchSizeCalculator
     */
    private $model;

    protected function setUp()
    {
        $this->model = new BatchSizeCalculator();
    }

    /**
     * @param int $memoryTableMinRows number of records that can be inserted into MEMORY table when its maximum size
     * @param int $maxHeapTableSize max_heap_table_size MySQL value
     * @param int $tmpTableSize tmp_table_size MySQL value
     * @param int $expectedResult
     *
     * @dataProvider estimateBatchSizeDataProvider
     */
    public function testEstimateBatchSize($memoryTableMinRows, $maxHeapTableSize, $tmpTableSize, $expectedResult)
    {
        $adapterMock = $this->getMock(AdapterInterface::class);
        $adapterMock->expects($this->at(0))
            ->method('fetchOne')
            ->with('SELECT @@max_heap_table_size;', [])
            ->willReturn($maxHeapTableSize);
        $adapterMock->expects($this->at(1))
            ->method('fetchOne')
            ->with('SELECT @@tmp_table_size;', [])
            ->willReturn($tmpTableSize);

        $this->assertEquals($expectedResult, $this->model->estimateBatchSize($adapterMock, $memoryTableMinRows));
    }

    /**
     * @return array
     */
    public function estimateBatchSizeDataProvider()
    {
        /**
         * The maximum size for in-memory temporary tables is determined from whichever of the values of
         * tmp_table_size and max_heap_table_size is smaller.
         */
        return [
            [200, 16384, 16384, 200],
            [200, 16384, 20000, 200],
            [200, 20000, 16384, 200],
            [200, 20000, 20000, 244],
            [200, 2000, 2000, 24],
        ];
    }
}
