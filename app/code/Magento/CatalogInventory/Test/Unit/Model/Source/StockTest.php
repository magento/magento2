<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Source;

use Magento\CatalogInventory\Model\Source\Stock;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    /**
     * @var Stock
     */
    private $model;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    protected function setUp(): void
    {
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Stock($this->metadataPool);
    }

    public function testAddValueSortToCollection()
    {
        $entityMetadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityMetadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($entityMetadata);

        $selectMock = $this->createMock(Select::class);
        $collectionMock = $this->createMock(AbstractCollection::class);
        $collectionMock->expects($this->atLeastOnce())->method('getSelect')->willReturn($selectMock);
        $collectionMock->expects($this->atLeastOnce())->method('getTable')->willReturn('cataloginventory_stock_item');
        $collectionMock->expects($this->exactly(3))->method('joinField')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['child_id'] => $collectionMock,
                ['child_stock'] => $collectionMock,
                ['parent_stock'] => $collectionMock
            });

        $selectMock->expects($this->once())
            ->method('group')
            ->with('e.entity_id')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('order')
            ->with('stock DESC')
            ->willReturnSelf();

        $this->model->addValueSortToCollection($collectionMock);
    }
}
