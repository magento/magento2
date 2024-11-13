<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type\Configurable;

use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as AttributeModel;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    /** @var  MockObject */
    protected $connection;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resource;

    /**
     * @var Relation|MockObject
     */
    protected $relation;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();

        $this->resource = $this->createMock(ResourceConnection::class);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->resource->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->attribute = $this->objectManagerHelper->getObject(
            Attribute::class,
            [
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSaveNewLabel(): void
    {
        $attributeId = 4354;

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select
            ->method('where')
            ->willReturnOnConsecutiveCalls($select, $select);
        $this->connection->expects($this->once())->method('fetchOne')->with(
            $select,
            [
                'product_super_attribute_id' => $attributeId,
                'store_id' => Store::DEFAULT_STORE_ID,
            ]
        )->willReturn(0);

        $this->connection->expects($this->once())->method('insert')->with(
            'catalog_product_super_attribute_label',
            [
                'product_super_attribute_id' => $attributeId,
                'use_default' => 0,
                'store_id' => 0,
                'value' => 'test',
            ]
        );
        $attributeMock = $this->getMockBuilder(AttributeModel::class)
            ->onlyMethods(['getId', 'getLabel'])
            ->addMethods(['getUseDefault'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->atLeastOnce())->method('getId')->willReturn($attributeId);
        $attributeMock->expects($this->atLeastOnce())->method('getUseDefault')->willReturn(0);
        $attributeMock->expects($this->atLeastOnce())->method('getLabel')->willReturn('test');
        $this->assertEquals($this->attribute, $this->attribute->saveLabel($attributeMock));
    }

    /**
     * @return void
     */
    public function testSaveExistingLabel(): void
    {
        $attributeId = 4354;
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select
            ->method('where')
            ->willReturnOnConsecutiveCalls($select, $select);
        $this->connection->expects($this->once())->method('fetchOne')->with(
            $select,
            [
                'product_super_attribute_id' => $attributeId,
                'store_id' => Store::DEFAULT_STORE_ID
            ]
        )->willReturn(1);

        $this->connection->expects($this->once())->method('insertOnDuplicate')->with(
            'catalog_product_super_attribute_label',
            [
                'product_super_attribute_id' => $attributeId,
                'use_default' => 0,
                'store_id' => 1,
                'value' => 'test'
            ]
        );
        $attributeMock = $this->getMockBuilder(AttributeModel::class)
            ->onlyMethods(['getId', 'getLabel'])
            ->addMethods(['getUseDefault', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->atLeastOnce())->method('getId')->willReturn($attributeId);
        $attributeMock->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $attributeMock->expects($this->atLeastOnce())->method('getUseDefault')->willReturn(0);
        $attributeMock->expects($this->atLeastOnce())->method('getLabel')->willReturn('test');
        $this->assertEquals($this->attribute, $this->attribute->saveLabel($attributeMock));
    }
}
