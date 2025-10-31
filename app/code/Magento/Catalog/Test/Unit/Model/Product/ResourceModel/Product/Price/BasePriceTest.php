<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\ResourceModel\Product\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Price\BasePrice;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BasePriceTest extends TestCase
{
    /**
     * @var Attribute|MockObject
     */
    private $attributeResource;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var EntityMetadataInterface|MockObject
     */
    private $entityMetadata;

    /**
     * @var BasePrice
     */
    private $model;

    protected function setUp(): void
    {
        $this->attributeResource = $this->createMock(Attribute::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->entityMetadata = $this->createMock(EntityMetadataInterface::class);

        $this->model = new BasePrice(
            $this->attributeResource,
            $this->metadataPool,
            5
        );
    }

    public function testUpdate()
    {
        $priceBunches = [
            ['store_id' => 0, 'row_id' => 1, 'value' => 15, 'attribute_id' => 5],
            ['store_id' => 0, 'row_id' => 2, 'value' => 20, 'attribute_id' => 5],
            ['store_id' => 1, 'row_id' => 1, 'value' => 15, 'attribute_id' => 5],
            ['store_id' => 1, 'row_id' => 2, 'value' => 20, 'attribute_id' => 5]
        ];

        $connection = $this->createMock(AdapterInterface::class);
        $select = $this->createMock(Select::class);
        $this->attributeResource->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($connection);

        $this->attributeResource->expects($this->atLeastOnce())
            ->method('getTable')
            ->with('catalog_product_entity_decimal')
            ->willReturn('catalog_product_entity_decimal');

        $select->expects($this->once())->method('from')->with('catalog_product_entity_decimal')->willReturnSelf();
        $select->expects($this->atLeastOnce())->method('where')->willReturnSelf();

        $connection->expects($this->once())
            ->method('beginTransaction');

        $connection->expects($this->once())
            ->method('commit');

        $connection->expects($this->atLeastOnce())
            ->method('update');

        $connection->expects($this->atLeastOnce())
            ->method('insertMultiple')
            ->willReturn(1);

        $connection->expects($this->once())
            ->method('select')
            ->willReturn($select);

        $connection->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['value_id' => 1, 'store_id' => 1, 'row_id' => 1, 'attribute_id' => 5],
                ['value_id' => 2, 'store_id' => 2, 'row_id' => 2, 'attribute_id' => 5]
            ]);

        $this->metadataPool->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->entityMetadata);

        $this->entityMetadata->expects($this->atLeastOnce())
            ->method('getLinkField')
            ->willReturn('row_id');

        $this->model->update($priceBunches);
    }

    public function testUpdateThrowsException()
    {
        $this->expectException(CouldNotSaveException::class);

        $priceBunches = [
            ['store_id' => 1, 'row_id' => 1, 'value' => 15, 'attribute_id' => 5]
        ];

        $connection = $this->createMock(AdapterInterface::class);
        $select = $this->createMock(Select::class);
        $this->attributeResource->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($connection);

        $this->attributeResource->expects($this->atLeastOnce())
            ->method('getTable')
            ->with('catalog_product_entity_decimal')
            ->willReturn('catalog_product_entity_decimal');

        $select->expects($this->once())->method('from')->with('catalog_product_entity_decimal')->willReturnSelf();
        $select->expects($this->atLeastOnce())->method('where')->willReturnSelf();

        $connection->expects($this->once())
            ->method('beginTransaction');

        $connection->expects($this->once())
            ->method('commit');

        $connection->expects($this->atLeastOnce())
            ->method('update');

        $connection->expects($this->once())
            ->method('select')
            ->willReturn($select);

        $connection->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['value_id' => 1, 'store_id' => 1, 'row_id' => 1, 'attribute_id' => 5],
                ['value_id' => 2, 'store_id' => 2, 'row_id' => 2, 'attribute_id' => 5]
            ]);

        $this->metadataPool->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->entityMetadata);

        $this->entityMetadata->expects($this->atLeastOnce())
            ->method('getLinkField')
            ->willReturn('row_id');

        $connection->expects($this->once())
            ->method('commit')
            ->willThrowException(new \Exception());

        $this->model->update($priceBunches);
    }
}
