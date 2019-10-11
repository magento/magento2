<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

/**
 * Class for Collection test.
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $_model;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Data\Collection(
            $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class)
        );
    }

    /**
     * Test for method removeAllItems.
     *
     * @return void
     */
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
     *
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
     * Test for method etItemObjectClass
     *
     * @dataProvider setItemObjectClassDataProvider
     */
    public function testSetItemObjectClass($class)
    {
        $this->_model->setItemObjectClass($class);
        $this->assertAttributeSame($class, '_itemObjectClass', $this->_model);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function setItemObjectClassDataProvider()
    {
        return [[\Magento\Framework\Url::class], [\Magento\Framework\DataObject::class]];
    }

    /**
     * Test for method setItemObjectClass with exception.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect_ClassName does not extend \Magento\Framework\DataObject
     */
    public function testSetItemObjectClassException()
    {
        $this->_model->setItemObjectClass('Incorrect_ClassName');
    }

    /**
     * Test for method addFilter.
     *
     * @return void
     */
    public function testAddFilter()
    {
        $this->_model->addFilter('field1', 'value');
        $this->assertEquals('field1', $this->_model->getFilter('field1')->getData('field'));
    }

    /**
     * Test for method getFilters.
     *
     * @return void
     */
    public function testGetFilters()
    {
        $this->_model->addFilter('field1', 'value');
        $this->_model->addFilter('field2', 'value');
        $this->assertEquals('field1', $this->_model->getFilter(['field1', 'field2'])[0]->getData('field'));
        $this->assertEquals('field2', $this->_model->getFilter(['field1', 'field2'])[1]->getData('field'));
    }

    /**
     * Test for method get non existion filters.
     *
     * @return void
     */
    public function testGetNonExistingFilters()
    {
        $this->assertEmpty($this->_model->getFilter([]));
        $this->assertEmpty($this->_model->getFilter('non_existing_filter'));
    }

    /**
     * Test for lag.
     *
     * @return void
     */
    public function testFlag()
    {
        $this->_model->setFlag('flag_name', 'flag_value');
        $this->assertEquals('flag_value', $this->_model->getFlag('flag_name'));
        $this->assertTrue($this->_model->hasFlag('flag_name'));
        $this->assertNull($this->_model->getFlag('non_existing_flag'));
    }

    /**
     * Test for method getCurPage.
     *
     * @return void
     */
    public function testGetCurPage()
    {
        $this->_model->setCurPage(1);
        $this->assertEquals(1, $this->_model->getCurPage());
    }

    /**
     * Test for method possibleFlowWithItem.
     *
     * @return void
     */
    public function testPossibleFlowWithItem()
    {
        $firstItemMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getId', 'getData', 'toArray']
        );
        $secondItemMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getId', 'getData', 'toArray']
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

    /**
     * Test for method eachCallsMethodOnEachItemWithNoArgs.
     *
     * @return void
     */
    public function testEachCallsMethodOnEachItemWithNoArgs()
    {
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createPartialMock(\Magento\Framework\DataObject::class, ['testCallback']);
            $item->expects($this->once())->method('testCallback')->with();
            $this->_model->addItem($item);
        }
        $this->_model->each('testCallback');
    }

    /**
     * Test for method eachCallsMethodOnEachItemWithArgs.
     *
     * @return void
     */
    public function testEachCallsMethodOnEachItemWithArgs()
    {
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createPartialMock(\Magento\Framework\DataObject::class, ['testCallback']);
            $item->expects($this->once())->method('testCallback')->with('a', 'b', 'c');
            $this->_model->addItem($item);
        }
        $this->_model->each('testCallback', ['a', 'b', 'c']);
    }

    /**
     * Test for method callsClosureWithEachItemAndNoArgs.
     *
     * @return void
     */
    public function testCallsClosureWithEachItemAndNoArgs()
    {
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createPartialMock(\Magento\Framework\DataObject::class, ['testCallback']);
            $item->expects($this->once())->method('testCallback')->with();
            $this->_model->addItem($item);
        }
        $this->_model->each(function ($item) {
            $item->testCallback();
        });
    }

    /**
     * Test for method callsClosureWithEachItemAndArgs.
     *
     * @return void
     */
    public function testCallsClosureWithEachItemAndArgs()
    {
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createPartialMock(\Magento\Framework\DataObject::class, ['testItemCallback']);
            $item->expects($this->once())->method('testItemCallback')->with('a', 'b', 'c');
            $this->_model->addItem($item);
        }
        $this->_model->each(function ($item, ...$args) {
            $item->testItemCallback(...$args);
        }, ['a', 'b', 'c']);
    }

    /**
     * Test for method callsCallableArrayWithEachItemNoArgs.
     *
     * @return void
     */
    public function testCallsCallableArrayWithEachItemNoArgs()
    {
        $mockCallbackObject = $this->getMockBuilder('DummyEachCallbackInstance')
            ->setMethods(['testObjCallback'])
            ->getMock();
        $mockCallbackObject->method('testObjCallback')->willReturnCallback(function ($item, ...$args) {
            $item->testItemCallback(...$args);
        });

        for ($i = 0; $i < 3; $i++) {
            $item = $this->createPartialMock(\Magento\Framework\DataObject::class, ['testItemCallback']);
            $item->expects($this->once())->method('testItemCallback')->with();
            $this->_model->addItem($item);
        }

        $this->_model->each([$mockCallbackObject, 'testObjCallback']);
    }

    /**
     * Test for method callsCallableArrayWithEachItemAndArgs.
     *
     * @return void
     */
    public function testCallsCallableArrayWithEachItemAndArgs()
    {
        $mockCallbackObject = $this->getMockBuilder('DummyEachCallbackInstance')
            ->setMethods(['testObjCallback'])
            ->getMock();
        $mockCallbackObject->method('testObjCallback')->willReturnCallback(function ($item, ...$args) {
            $item->testItemCallback(...$args);
        });

        for ($i = 0; $i < 3; $i++) {
            $item = $this->createPartialMock(\Magento\Framework\DataObject::class, ['testItemCallback']);
            $item->expects($this->once())->method('testItemCallback')->with('a', 'b', 'c');
            $this->_model->addItem($item);
        }

        $callback = [$mockCallbackObject, 'testObjCallback'];
        $this->_model->each($callback, ['a', 'b', 'c']);
    }
}
