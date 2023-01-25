<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\LinkedProductSelectBuilderByIndexPrice;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkedProductSelectBuilderByIndexPriceTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var BaseSelectProcessorInterface|MockObject
     */
    private $baseSelectProcessorMock;

    /**
     * @var IndexScopeResolverInterface|MockObject
     */
    private $indexScopeResolverMock;

    /**
     * @var Dimension|MockObject
     */
    private $dimensionMock;

    /**
     * @var DimensionFactory|MockObject
     */
    private $dimensionFactoryMock;

    /**
     * @var LinkedProductSelectBuilderByIndexPrice
     */
    private $model;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->baseSelectProcessorMock =
            $this->getMockBuilder(BaseSelectProcessorInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

        $this->indexScopeResolverMock = $this->createMock(
            IndexScopeResolverInterface::class
        );
        $this->dimensionMock = $this->createMock(Dimension::class);
        $this->dimensionFactoryMock = $this->createMock(DimensionFactory::class);
        $this->dimensionFactoryMock->method('create')->willReturn($this->dimensionMock);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->model = new LinkedProductSelectBuilderByIndexPrice(
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
        $storeId = 1;
        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
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
        $this->model->build($productId, $storeId);
    }
}
