<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type\Configurable;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\StockStatus;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;

class StockStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var string
     */
    private $productTable = 'catalog_product_entity';

    /**
     * @var string
     */
    private $productRelationTable = 'catalog_product_relation';

    /**
     * @var string
     */
    private $catalogInventoryTable = 'cataloginventory_stock_status';

    /**
     * @var int
     */
    private $productId = 1;

    /**
     * @var StockStatus
     */
    private $subject;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(AdapterInterface::class)->getMock();

        $this->resource = $this->createMock(ResourceConnection::class);
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($this->connection));
        $this->resource->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $this->metadataPool = $this->createMock(MetadataPool::class);
        $entityMetadataMock = $this->createMock(EntityMetadata::class);
        $entityMetadataMock->method('getLinkField')->willReturn(\Magento\Eav\Model\Entity::DEFAULT_ENTITY_ID_FIELD);
        $this->metadataPool->method('getMetadata')->willReturn($entityMetadataMock);

        $this->subject = (new ObjectManager($this))->getObject(
            StockStatus::class,
            [
                'resource' => $this->resource,
                'metadataPool' => $this->metadataPool
            ]
        );
    }

    /**
     * @param array $childStockInfo
     * @param bool $result
     *
     * @throws \Exception
     *
     * @dataProvider processDataProvider
     */
    public function testIsAllChildOutOfStock($childStockInfo, $result)
    {
        /** @var Select|\PHPUnit_Framework_MockObject_MockObject $selectMock */
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock->expects($this->once())
            ->method('from')
            ->with(['parent' => $this->productTable], '', [])
            ->willReturnSelf();

        $selectMock->expects($this->exactly(2))
            ->method('joinInner')
            ->withConsecutive(
                [
                    ['link' => $this->productRelationTable],
                    "link.parent_id = parent." . \Magento\Eav\Model\Entity::DEFAULT_ENTITY_ID_FIELD,
                    ['id' => 'child_id']
                ],
                [
                    ['stock' => $this->catalogInventoryTable],
                    'stock.product_id = link.child_id',
                    ['stock_status']
                ]
            )->willReturnSelf();

        $selectMock->expects($this->once())
            ->method('where')
            ->with(
                sprintf('parent.%s = ?', \Magento\Eav\Model\Entity::DEFAULT_ENTITY_ID_FIELD),
                $this->productId
            )
            ->willReturnSelf();

        $this->connection->method('select')->willReturn($selectMock);
        $this->connection->method('fetchPairs')->willReturn($childStockInfo);

        $this->assertEquals($result, $this->subject->isAllChildOutOfStock($this->productId));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'All child out of stock' => [
                [
                    '1' => StockStatusInterface::STATUS_OUT_OF_STOCK,
                    '2' => StockStatusInterface::STATUS_OUT_OF_STOCK,
                    '3' => StockStatusInterface::STATUS_OUT_OF_STOCK
                ],
                true
            ],
            'NOT all child out of stock' => [
                [
                    '1' => StockStatusInterface::STATUS_OUT_OF_STOCK,
                    '2' => StockStatusInterface::STATUS_IN_STOCK,
                    '3' => StockStatusInterface::STATUS_IN_STOCK
                ],
                false
            ]
        ];
    }
}
