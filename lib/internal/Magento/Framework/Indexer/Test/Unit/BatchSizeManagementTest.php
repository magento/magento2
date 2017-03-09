<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\Indexer\BatchSizeManagement;

class BatchSizeManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BatchSizeManagement
     */
    private $model;

    /**
     * @var \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rowSizeEstimatorMock;

    protected function setUp()
    {
        $this->rowSizeEstimatorMock = $this->getMock(
            \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface::class
        );
        $this->model = new BatchSizeManagement($this->rowSizeEstimatorMock);
    }

    /**
     * @param int $batchSize number of records in the batch
     * @param int $maxHeapTableSize max_heap_table_size MySQL value
     * @param int $tmpTableSize tmp_table_size MySQL value
     * @param int $size
     *
     * @dataProvider estimateBatchSizeDataProvider
     */
    public function testEnsureBatchSize($batchSize, $maxHeapTableSize, $tmpTableSize, $size)
    {
        $this->rowSizeEstimatorMock->expects($this->once())->method('estimateRowSize')->willReturn(100);
        $adapterMock = $this->getMock(AdapterInterface::class);
        $adapterMock->expects($this->at(0))
            ->method('fetchOne')
            ->with('SELECT @@max_heap_table_size;', [])
            ->willReturn($maxHeapTableSize);
        $adapterMock->expects($this->at(1))
            ->method('fetchOne')
            ->with('SELECT @@tmp_table_size;', [])
            ->willReturn($tmpTableSize);

        $adapterMock->expects($this->at(2))
            ->method('query')
            ->with('SET SESSION tmp_table_size = ' . $size . ';', []);

        $adapterMock->expects($this->at(3))
            ->method('query')
            ->with('SET SESSION max_heap_table_size = ' . $size . ';', []);

        $this->model->ensureBatchSize($adapterMock, $batchSize);
    }

    /**
     * @return array
     */
    public function estimateBatchSizeDataProvider()
    {
        return [
            [200, 16384, 16384, 20000],
            [300, 16384, 20000, 30000],
            [400, 20000, 16384, 40000],
            [500, 2000, 2000, 50000],
        ];
    }
}
