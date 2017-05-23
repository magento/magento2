<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Data\Collection(
            $this->getMock(\Magento\Framework\Data\Collection\EntityFactory::class, [], [], '', false)
        );
    }

    public function testRemoveAllItems()
    {
        $this->_model->addItem(new \Magento\Framework\DataObject());
        $this->_model->addItem(new \Magento\Framework\DataObject());
        $this->assertCount(2, $this->_model->getItems());
        $this->_model->removeAllItems();
        $this->assertEmpty($this->_model->getItems());
    }

    /**
     * Test loadWithFilter()
     * @return void
     */
    public function testLoadWithFilter()
    {
        $this->assertInstanceOf(\Magento\Framework\Data\Collection::class, $this->_model->loadWithFilter());
        $this->assertEmpty($this->_model->getItems());
        $this->_model->addItem(new \Magento\Framework\DataObject());
        $this->_model->addItem(new \Magento\Framework\DataObject());
        $this->assertCount(2, $this->_model->loadWithFilter()->getItems());
    }

    /**
     * @dataProvider setItemObjectClassDataProvider
     */
    public function testSetItemObjectClass($class)
    {
        $this->_model->setItemObjectClass($class);
        $this->assertAttributeSame($class, '_itemObjectClass', $this->_model);
    }

    /**
     * @return array
     */
    public function setItemObjectClassDataProvider()
    {
        return [[\Magento\Framework\Url::class], [\Magento\Framework\DataObject::class]];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect_ClassName does not extend \Magento\Framework\DataObject
     */
    public function testSetItemObjectClassException()
    {
        $this->_model->setItemObjectClass('Incorrect_ClassName');
    }

    public function testAddFilter()
    {
        $this->_model->addFilter('field1', 'value');
        $this->assertEquals('field1', $this->_model->getFilter('field1')->getData('field'));
    }

    public function testGetFilters()
    {
        $this->_model->addFilter('field1', 'value');
        $this->_model->addFilter('field2', 'value');
        $this->assertEquals('field1', $this->_model->getFilter(['field1', 'field2'])[0]->getData('field'));
        $this->assertEquals('field2', $this->_model->getFilter(['field1', 'field2'])[1]->getData('field'));
    }

    public function testGetNonExistingFilters()
    {
        $this->assertEmpty($this->_model->getFilter([]));
        $this->assertEmpty($this->_model->getFilter('non_existing_filter'));
    }

    public function testFlag()
    {
        $this->_model->setFlag('flag_name', 'flag_value');
        $this->assertEquals('flag_value', $this->_model->getFlag('flag_name'));
        $this->assertTrue($this->_model->hasFlag('flag_name'));
        $this->assertNull($this->_model->getFlag('non_existing_flag'));
    }

    public function testGetCurPage()
    {
        $this->_model->setCurPage(10);
        $this->assertEquals(1, $this->_model->getCurPage());
    }

    public function testPossibleFlowWithItem()
    {
        $firstItemMock = $this->getMock(
            \Magento\Framework\DataObject::class,
            ['getId', 'getData', 'toArray'],
            [],
            '',
            false
        );
        $secondItemMock = $this->getMock(
            \Magento\Framework\DataObject::class,
            ['getId', 'getData', 'toArray'],
            [],
            '',
            false
        );
        $requiredFields = ['required_field_one', 'required_field_two'];
        $arrItems = [
            'totalRecords' => 1,
            'items' => [
                0 => 'value',
            ],
        ];
        $items = [
            'item_id' => $firstItemMock,
            0 => $secondItemMock,
        ];
        $firstItemMock->expects($this->exactly(2))->method('getId')->will($this->returnValue('item_id'));

        $firstItemMock
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->with('colName')
            ->will($this->returnValue('first_value'));
        $secondItemMock
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->with('colName')
            ->will($this->returnValue('second_value'));

        $firstItemMock
            ->expects($this->once())
            ->method('toArray')
            ->with($requiredFields)
            ->will($this->returnValue('value'));
        /** add items and set them values */
        $this->_model->addItem($firstItemMock);
        $this->assertEquals($arrItems, $this->_model->toArray($requiredFields));

        $this->_model->addItem($secondItemMock);
        $this->_model->setDataToAll('column', 'value');

        /** get items by column name */
        $this->assertEquals(['first_value', 'second_value'], $this->_model->getColumnValues('colName'));
        $this->assertEquals([$secondItemMock], $this->_model->getItemsByColumnValue('colName', 'second_value'));
        $this->assertEquals($firstItemMock, $this->_model->getItemByColumnValue('colName', 'second_value'));
        $this->assertEquals([], $this->_model->getItemsByColumnValue('colName', 'non_existing_value'));
        $this->assertEquals(null, $this->_model->getItemByColumnValue('colName', 'non_existing_value'));

        /** get items */
        $this->assertEquals(['item_id', 0], $this->_model->getAllIds());
        $this->assertEquals($firstItemMock, $this->_model->getFirstItem());
        $this->assertEquals($secondItemMock, $this->_model->getLastItem());
        $this->assertEquals($items, $this->_model->getItems('item_id'));

        /** remove existing items */
        $this->assertNull($this->_model->getItemById('not_existing_item_id'));
        $this->_model->removeItemByKey('item_id');
        $this->assertEquals([$secondItemMock], $this->_model->getItems());
        $this->_model->removeAllItems();
        $this->assertEquals([], $this->_model->getItems());
    }

    public function testMap()
    {
        $item1 = new \Magento\Framework\DataObject();
        $item2 = new \Magento\Framework\DataObject();

        $this->_model->addItem($item1);
        $this->_model->addItem($item2);
        $this->assertCount(2, $this->_model->getItems());

        $mapped = $this->_model->map(function (\Magento\Framework\DataObject $obj) {
            return $obj->setData('map_test', true);
        });

        $this->assertNotSame($mapped, $this->_model);
        $this->assertTrue($item1->getData('map_test'));
        $this->assertTrue($item2->getData('map_test'));
    }

    public function testFilter()
    {
        $item1 = new \Magento\Framework\DataObject(['price' => 10]);
        $item2 = new \Magento\Framework\DataObject(['price' => 20]);
        $item3 = new \Magento\Framework\DataObject(['price' => 30]);

        $this->_model->addItem($item1);
        $this->_model->addItem($item2);
        $this->_model->addItem($item3);

        $this->assertCount(3, $this->_model->getItems());

        $filtered = $this->_model->filter(function (\Magento\Framework\DataObject $obj) {
            return $obj->getData('price') >= 20;
        });

        $this->assertNotSame($filtered, $this->_model);
        $this->assertCount(2, $filtered->getItems());
        $this->assertCount(3, $this->_model->getItems());

    }

    public function testReduce()
    {
        $item1 = new \Magento\Framework\DataObject(['price' => 10]);
        $item2 = new \Magento\Framework\DataObject(['price' => 20]);
        $item3 = new \Magento\Framework\DataObject(['price' => 30]);

        $this->_model->addItem($item1);
        $this->_model->addItem($item2);
        $this->_model->addItem($item3);

        $this->assertCount(3, $this->_model->getItems());

        $totalPrice = $this->_model->reduce(function ($carry, \Magento\Framework\DataObject $obj) {
            return $carry + $obj->getData('price');
        }, 0);

        $this->assertEquals(60, $totalPrice);
    }
}
