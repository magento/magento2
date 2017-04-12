<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product\Type\Configurable;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as AttributeModel;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
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
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relation;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)->getMock();

        $this->resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($this->connection));
        $this->resource->expects($this->any())->method('getTableName')->willReturnArgument(0);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->attribute = $this->objectManagerHelper->getObject(
            Attribute::class,
            [
                'resource' => $this->resource,
            ]
        );
    }

    public function testSaveLabel()
    {
        $attributeId = 4354;

        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select->expects($this->at(1))->method('where')->willReturnSelf();
        $select->expects($this->at(2))->method('where')->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchOne')->with(
            $select,
            [
                'product_super_attribute_id' => $attributeId,
                'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ]
        )->willReturn(0);

        $this->connection->expects($this->once())->method('insertOnDuplicate')->with(
            'catalog_product_super_attribute_label',
            [
                'product_super_attribute_id' => $attributeId,
                'use_default' => 0,
                'store_id' => 0,
                'value' => 'test',
            ]
        );
        $attributeMode = $this->getMockBuilder(AttributeModel::class)->setMethods(
            ['getId', 'getUseDefault', 'getLabel']
        )->disableOriginalConstructor()->getMock();
        $attributeMode->expects($this->any())->method('getId')->willReturn($attributeId);
        $attributeMode->expects($this->any())->method('getUseDefault')->willReturn(0);
        $attributeMode->expects($this->any())->method('getLabel')->willReturn('test');
        $this->assertEquals($this->attribute, $this->attribute->saveLabel($attributeMode));
    }
}
