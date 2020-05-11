<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\BatchSizeManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BatchSizeCalculatorTest extends TestCase
{
    /**
     * @var BatchSizeCalculator
     */
    private $model;

    /**
     * @var BatchSizeManagementInterface|MockObject
     */
    private $estimatorMock;

    /**
     * @var int
     */
    private $batchRowsCount;

    protected function setUp(): void
    {
        $this->estimatorMock = $this->getMockForAbstractClass(BatchSizeManagementInterface::class);
        $this->batchRowsCount = 200;
        $this->model = new BatchSizeCalculator(
            ['default' => $this->batchRowsCount],
            ['default' => $this->estimatorMock],
            []
        );
    }

    public function testEstimateBatchSize()
    {
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $typeId = 'default';
        $batchSize = 100500;

        $this->estimatorMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($connectionMock, $this->batchRowsCount)
            ->willReturn($batchSize);

        $this->model->estimateBatchSize($connectionMock, $typeId);
    }
}
