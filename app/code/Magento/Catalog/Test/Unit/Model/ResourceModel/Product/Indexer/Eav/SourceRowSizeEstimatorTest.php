<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceRowSizeEstimator;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source;
use Magento\Store\Api\StoreManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\DB\Select;

class SourceRowSizeEstimatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SourceRowSizeEstimator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    protected function setUp()
    {
        $this->connectionMock = $this->getMock(AdapterInterface::class);
        $this->indexerResourceMock = $this->getMock(Source::class, [], [], '', false);
        $this->indexerResourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->storeManagementMock = $this->getMock(StoreManagementInterface::class);
        $this->metadataPoolMock = $this->getMock(MetadataPool::class, [], [], '', false);

        $this->model = new SourceRowSizeEstimator(
            $this->storeManagementMock,
            $this->indexerResourceMock,
            $this->metadataPoolMock
        );
    }

    public function testEstimateRowSize()
    {
        $entityMetadataMock = $this->getMock(EntityMetadataInterface::class);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);

        $selectMock = $this->getMock(Select::class, [], [], '', false);

        $maxRowsPerStoreInt = 100;
        $maxRowsPerStoreVarchar = 200;
        $storeCount = 10;
        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->at(1))->method('fetchOne')->willReturn($maxRowsPerStoreInt);
        $this->connectionMock->expects($this->at(2))->method('fetchOne')->willReturn($maxRowsPerStoreVarchar);
        $this->storeManagementMock->expects($this->any())->method('getCount')->willReturn($storeCount);

        $this->assertEquals($maxRowsPerStoreVarchar * $storeCount * 500, $this->model->estimateRowSize());
    }
}
