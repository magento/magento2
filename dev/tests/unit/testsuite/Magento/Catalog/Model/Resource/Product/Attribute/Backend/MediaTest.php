<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Product\Attribute\Backend;

/**
 * Test Media Resource
 *
 * Class MediaTest
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readAdapter;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Media | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\DB\Select | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->readAdapter = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->readAdapter);
        $resource->expects($this->any())->method('getTableName')->willReturn('table');
        $this->readAdapter->expects($this->any())->method('setCacheAdapter');
        $this->resource = $objectManager->getObject(
            'Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media',
            ['resource' => $resource]
        );
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->model = $this->getMock('Magento\Catalog\Model\Product\Attribute\Backend\Media', [], [], '', false);
        $this->select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $this->attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', [], [], '', false);
    }

    public function testLoadGallery()
    {
        $productId = 5;
        $storeId = 1;
        $attributeId = 6;
        $getTableReturnValue = 'table';
        $quoteInfoReturnValue = 'main.value_id = value.value_id AND value.store_id = ' . $storeId;
        $positionCheckSql = 'testchecksql';
        $resultRow = [
            [
                'value_id' => '1',
                'file' => '/d/o/download_7.jpg',
                'label' => null,
                'position' => '1',
                'disabled' => '0',
                'label_default' => null,
                'position_default' => '1',
                'disabled_default' => '0',
            ],
        ];

        $this->readAdapter->expects($this->once())->method('getCheckSql')->with(
            'value.position IS NULL',
            'default_value.position',
            'value.position'
        )->will($this->returnValue($positionCheckSql));
        $this->readAdapter->expects($this->once())->method('select')->will($this->returnValue($this->select));
        $this->select->expects($this->at(0))->method('from')->with(
            [
                'main' => $getTableReturnValue,
            ],
            [
                'value_id',
                'file' => 'value'
            ]
        )->willReturnSelf();

        $this->product->expects($this->at(0))->method('getStoreId')->will($this->returnValue($storeId));
        $this->readAdapter->expects($this->once())->method('quoteInto')
            ->with('main.value_id = value.value_id AND value.store_id = ?', $storeId)
            ->will($this->returnValue($quoteInfoReturnValue));
        $this->select->expects($this->at(1))->method('joinLeft')->with(
            ['value' => $getTableReturnValue],
            $quoteInfoReturnValue,
            [
                'label',
                'position',
                'disabled'
            ])->willReturnSelf();
        $this->select->expects($this->at(2))->method('joinLeft')->with(
            ['default_value' => $getTableReturnValue],
            'main.value_id = default_value.value_id AND default_value.store_id = 0',
            ['label_default' => 'label', 'position_default' => 'position', 'disabled_default' => 'disabled']
        )->willReturnSelf();
        $this->model->expects($this->at(0))->method('getAttribute')->will($this->returnValue($this->attribute));
        $this->attribute->expects($this->at(0))->method('getId')->will($this->returnValue($attributeId));
        $this->select->expects($this->at(3))->method('where')->with(
            'main.attribute_id = ?',
            $attributeId
        )->willReturnSelf();
        $this->product->expects($this->at(1))->method('getId')->willReturn($productId);
        $this->select->expects($this->at(4))->method('where')->with(
            'main.entity_id = ?',
            $productId
        )->willReturnSelf();
        $this->select->expects($this->at(5))->method('where')
            ->with($positionCheckSql . ' IS NOT NULL')
            ->willReturnSelf();
        $this->select->expects($this->once())->method('order')
            ->with($positionCheckSql . ' ' . \Magento\Framework\DB\Select::SQL_ASC)
            ->willReturnSelf();
        $this->readAdapter->expects($this->once())->method('fetchAll')
            ->with($this->select)
            ->willReturn($resultRow);

        $this->assertEquals($resultRow, $this->resource->loadGallery($this->product, $this->model));
    }
}
