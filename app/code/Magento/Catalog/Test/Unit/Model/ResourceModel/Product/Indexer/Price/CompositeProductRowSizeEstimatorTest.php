<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRowSizeEstimator;

class CompositeProductRowSizeEstimatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeProductRowSizeEstimator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultPriceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    protected function setUp()
    {
        $this->websiteManagementMock = $this->getMock(
            \Magento\Store\Api\WebsiteManagementInterface::class,
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Group\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->defaultPriceMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice::class,
            [],
            [],
            '',
            false
        );
        $this->model = new CompositeProductRowSizeEstimator(
            $this->defaultPriceMock,
            $this->websiteManagementMock,
            $this->collectionFactoryMock
        );
    }

    public function testEstimateRowSize()
    {
        $expectedResult = 40000000;
        $tableName = 'catalog_product_relation';
        $maxRelatedProductCount = 10;

        $this->websiteManagementMock->expects($this->once())->method('getCount')->willReturn(100);
        $collectionMock = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Group\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(200);

        $connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->defaultPriceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->defaultPriceMock->expects($this->once())->method('getTable')->with($tableName)->willReturn($tableName);

        $relationSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $relationSelectMock->expects($this->once())
            ->method('from')
            ->with(
                ['relation' => $tableName],
                ['count' => 'count(relation.child_id)']
            )
            ->willReturnSelf();
        $relationSelectMock->expects($this->once())->method('group')->with('parent_id')->willReturnSelf();
        $connectionMock->expects($this->at(0))->method('select')->willReturn($relationSelectMock);

        $maxSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $maxSelectMock->expects($this->once())
            ->method('from')
            ->with(
                ['max_value' => $relationSelectMock],
                ['count' => 'MAX(count)']
            )
            ->willReturnSelf();
        $connectionMock->expects($this->at(1))->method('select')->willReturn($maxSelectMock);

        $connectionMock->expects($this->at(2))
            ->method('fetchOne')
            ->with($maxSelectMock)
            ->willReturn($maxRelatedProductCount);

        $this->assertEquals(
            $expectedResult,
            $this->model->estimateRowSize()
        );
    }
}
