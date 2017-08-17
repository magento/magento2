<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StatusTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\Product\Attribute\Source\Status */
    protected $status;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject */
    protected $collection;

    /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeModel;

    /** @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendAttributeModel;

    /**
     * @var AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entity;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->collection = $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, [
                '__wakeup',
                'getSelect',
                'joinLeft',
                'order',
                'getStoreId',
                'getConnection',
                'getCheckSql'
            ]);
        $this->attributeModel = $this->createPartialMock(\Magento\Catalog\Model\Entity\Attribute::class, [
                '__wakeup',
                'getAttributeCode',
                'getBackend',
                'getId',
                'isScopeGlobal',
                'getEntity',
                'getAttribute'
            ]);
        $this->backendAttributeModel = $this->createPartialMock(\Magento\Catalog\Model\Product\Attribute\Backend\Sku::class, ['__wakeup', 'getTable']);
        $this->status = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::class
        );

        $this->attributeModel->expects($this->any())->method('getAttribute')
            ->will($this->returnSelf());
        $this->attributeModel->expects($this->any())->method('getAttributeCode')
            ->will($this->returnValue('attribute_code'));
        $this->attributeModel->expects($this->any())->method('getId')
            ->will($this->returnValue('1'));
        $this->attributeModel->expects($this->any())->method('getBackend')
            ->will($this->returnValue($this->backendAttributeModel));
        $this->collection->expects($this->any())->method('getSelect')
            ->will($this->returnSelf());
        $this->collection->expects($this->any())->method('joinLeft')
            ->will($this->returnSelf());
        $this->backendAttributeModel->expects($this->any())->method('getTable')
            ->will($this->returnValue('table_name'));

        $this->entity = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
    }

    public function testAddValueSortToCollectionGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(true));
        $this->collection->expects($this->once())->method('order')->with('attribute_code_t.value asc')
            ->will($this->returnSelf());

        $this->attributeModel->expects($this->once())->method('getEntity')->willReturn($this->entity);
        $this->entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');

        $this->status->setAttribute($this->attributeModel);
        $this->status->addValueSortToCollection($this->collection);
    }

    public function testAddValueSortToCollectionNotGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(false));

        $this->collection->expects($this->once())->method('order')->with('check_sql asc')
            ->will($this->returnSelf());
        $this->collection->expects($this->once())->method('getStoreId')
            ->will($this->returnValue(1));
        $this->collection->expects($this->any())->method('getConnection')
            ->will($this->returnSelf());
        $this->collection->expects($this->any())->method('getCheckSql')
            ->will($this->returnValue('check_sql'));

        $this->attributeModel->expects($this->any())->method('getEntity')->willReturn($this->entity);
        $this->entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');

        $this->status->setAttribute($this->attributeModel);
        $this->status->addValueSortToCollection($this->collection);
    }

    public function testGetVisibleStatusIds()
    {
        $this->assertEquals([0 => 1], $this->status->getVisibleStatusIds());
    }

    public function testGetSaleableStatusIds()
    {
        $this->assertEquals([0 => 1], $this->status->getSaleableStatusIds());
    }

    public function testGetOptionArray()
    {
        $this->assertEquals([1 => 'Enabled', 2 => 'Disabled'], $this->status->getOptionArray());
    }

    /**
     * @dataProvider getOptionTextDataProvider
     * @param string $text
     * @param string $id
     */
    public function testGetOptionText($text, $id)
    {
        $this->assertEquals($text, $this->status->getOptionText($id));
    }

    /**
     * @return array
     */
    public function getOptionTextDataProvider()
    {
        return [
            [
                'text' => 'Enabled',
                'id' => '1',
            ],
            [
                'text' => 'Disabled',
                'id' => '2'
            ]
        ];
    }
}
