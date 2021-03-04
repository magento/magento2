<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkedProductSelectBuilderByIndexPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    /**
     * @var BaseSelectProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $baseSelectProcessorMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\LinkedProductSelectBuilderByIndexPrice
     */
    private $model;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->baseSelectProcessorMock =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

        $this->indexScopeResolverMock = $this->createMock(
            \Magento\Framework\Search\Request\IndexScopeResolverInterface::class
        );
        $this->dimensionMock = $this->createMock(\Magento\Framework\Indexer\Dimension::class);
        $this->dimensionFactoryMock = $this->createMock(\Magento\Framework\Indexer\DimensionFactory::class);
        $this->dimensionFactoryMock->method('create')->willReturn($this->dimensionMock);
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->model = new \Magento\Catalog\Model\ResourceModel\Product\Indexer\LinkedProductSelectBuilderByIndexPrice(
            $this->storeManagerMock,
            $this->resourceMock,
            $this->customerSessionMock,
            $this->metadataPoolMock,
            $this->baseSelectProcessorMock,
            $this->indexScopeResolverMock,
            $this->dimensionFactoryMock
        );
    }

    public function testBuild()
    {
        $productId = 10;
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn(1);
        $connection->expects($this->any())->method('select')->willReturn($select);
        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinInner')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->exactly(2))->method('order')->willReturnSelf();
        $select->expects($this->once())->method('limit')->willReturnSelf();
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($connection);
        $this->metadataPoolMock->expects($this->once())->method('getMetadata')->willReturn($metadata);
        $metadata->expects($this->once())->method('getLinkField')->willReturn('row_id');
        $this->resourceMock->expects($this->any())->method('getTableName');
        $this->baseSelectProcessorMock->expects($this->once())->method('process')->willReturnSelf();
        $this->model->build($productId);
    }
}
