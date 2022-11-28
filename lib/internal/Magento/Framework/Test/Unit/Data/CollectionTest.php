<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Data;

use Exception;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    protected function setUp(): void
    {
        $factoryMock = $this->getMockForAbstractClass(EntityFactoryInterface::class);
        $this->collection = new Collection($factoryMock);
    }

    /**
     * Test that callback works correctly for all items in collection.
     * @see https://github.com/magento/magento2/pull/5742
     */
    public function testWalk()
    {
        $objOne = new DataObject(['id' => 1, 'name' => 'one']);
        $objTwo = new DataObject(['id' => 2, 'name' => 'two']);
        $objThree = new DataObject(['id' => 3, 'name' => 'three']);

        $this->collection->addItem($objOne);
        $this->collection->addItem($objTwo);
        $this->collection->addItem($objThree);

        $this->assertEquals([1, 2, 3], $this->collection->getAllIds(), 'Items added incorrectly to the collection');
        $this->collection->walk([$this, 'modifyObjectNames'], ['test prefix']);

        $this->assertEquals([1, 2, 3], $this->collection->getAllIds(), 'Incorrect IDs after callback function');
        $expectedNames = [
            'test prefix one',
            'test prefix two',
            'test prefix three'
        ];

        $this->assertEquals(
            $expectedNames,
            $this->collection->getColumnValues('name'),
            'Incorrect Names after callback function'
        );
    }

    /**
     * Test that callback function works correctly with associative array in method params on php 8.0
     *
     * @throws Exception
     */
    public function testWalkWithAssociativeArrayInParameter()
    {
        $elementOne = new DataObject(['id' => 1, 'name' => 'firstElement']);
        $elementTwo = new DataObject(['id' => 2, 'name' => 'secondElement']);
        $elementThree = new DataObject(['id' => 3, 'name' => 'thirdElement']);
        $this->collection->addItem($elementOne);
        $this->collection->addItem($elementTwo);
        $this->collection->addItem($elementThree);
        $this->collection->walk([$this, 'modifyObjectNames'], ['test prefix']);
        $expectedNames = [
            'test prefix firstElement',
            'test prefix secondElement',
            'test prefix thirdElement'
        ];
        $this->assertEquals($expectedNames, $this->collection->getColumnValues('name'));
    }

    /**
     * Ensure that getSize works correctly with clear
     *
     */
    public function testClearTotalRecords()
    {
        $objOne = new DataObject(['id' => 1, 'name' => 'one']);
        $objTwo = new DataObject(['id' => 2, 'name' => 'two']);
        $objThree = new DataObject(['id' => 3, 'name' => 'three']);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->collection->addItem($objOne);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->collection->addItem($objTwo);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->collection->addItem($objThree);
        $this->assertEquals(3, $this->collection->getSize());
        $this->collection->clear();
        $this->assertEquals(0, $this->collection->getSize());
    }

    /**
     * Callback function.
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $prefix
     */
    public function modifyObjectNames(DataObject $object, $prefix)
    {
        $object->setData('name', $prefix . ' ' . $object->getData('name'));
    }
}
