<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalRowSizeEstimator;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal;
use Magento\Store\Api\StoreManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\DB\Select;

class DecimalRowSizeEstimatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DecimalRowSizeEstimator
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $indexerResourceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->indexerResourceMock = $this->createMock(Decimal::class);
        $this->indexerResourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->storeManagementMock = $this->getMockForAbstractClass(StoreManagementInterface::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);

        $this->model = new DecimalRowSizeEstimator(
            $this->storeManagementMock,
            $this->indexerResourceMock,
            $this->metadataPoolMock
        );
    }

    public function testEstimateRowSize()
    {
        $entityMetadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);

        $selectMock = $this->createMock(Select::class);

        $maxRowsPerStore = 100;
        $storeCount = 10;
        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->willReturn($maxRowsPerStore);
        $this->storeManagementMock->expects($this->any())->method('getCount')->willReturn($storeCount);

        $this->assertEquals($maxRowsPerStore * $storeCount * 500, $this->model->estimateRowSize());
    }
}
