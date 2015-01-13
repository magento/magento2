<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class StatusTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->collection = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Collection',
            [
                '__wakeup',
                'getSelect',
                'joinLeft',
                'order',
                'getStoreId',
                'getConnection',
                'getCheckSql'
            ],
            [],
            '',
            false
        );
        $this->attributeModel = $this->getMock(
            '\Magento\Catalog\Model\Entity\Attributee',
            [
                '__wakeup',
                'getAttributeCode',
                'getBackend',
                'getId',
                'isScopeGlobal',
            ],
            [],
            '',
            false
        );
        $this->backendAttributeModel = $this->getMock(
            '\Magento\Catalog\Model\Product\Attribute\Backend\Sku', ['__wakeup', 'getTable'], [], '', false);
        $this->status = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product\Attribute\Source\Status'
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
    }

    public function testAddValueSortToCollectionGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(true));
        $this->collection->expects($this->once())->method('order')->with('attribute_code_t.value asc')
            ->will($this->returnSelf());

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
