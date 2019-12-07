<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

class BatchSizeCalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator
     */
    private $model;

    /**
     * @var \Magento\Framework\Indexer\BatchSizeManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $estimatorMock;

    /**
     * @var int
     */
    private $batchRowsCount;

    protected function setUp()
    {
        $this->estimatorMock = $this->createMock(\Magento\Framework\Indexer\BatchSizeManagementInterface::class);
        $this->batchRowsCount = 200;
        $this->model = new \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator(
            ['default' => $this->batchRowsCount],
            ['default' => $this->estimatorMock],
            []
        );
    }

    public function testEstimateBatchSize()
    {
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $typeId = 'default';
        $batchSize = 100500;

        $this->estimatorMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($connectionMock, $this->batchRowsCount)
            ->willReturn($batchSize);

        $this->model->estimateBatchSize($connectionMock, $typeId);
    }
}
