<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\Data;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

/**
 * Class CollectionTest
 * @package Magento\Framework\Test\Unit\Data
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    public function setUp()
    {
        $factoryMock = $this->createMock(EntityFactoryInterface::class);
        $this->collection = new Collection($factoryMock);
    }

    /**
     * Test that callback works correctly for all items in collection.
     * @see https://github.com/magento/magento2/pull/5742
     */
    public function testWalk()
    {
        $objOne = new \Magento\Framework\DataObject(['id' => 1, 'name' => 'one']);
        $objTwo = new \Magento\Framework\DataObject(['id' => 2, 'name' => 'two']);
        $objThree = new \Magento\Framework\DataObject(['id' => 3, 'name' => 'three']);

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
     * Ensure that getSize works correctly with clear
     *
     */
    public function testClearTotalRecords()
    {
        $objOne = new \Magento\Framework\DataObject(['id' => 1, 'name' => 'one']);
        $objTwo = new \Magento\Framework\DataObject(['id' => 2, 'name' => 'two']);
        $objThree = new \Magento\Framework\DataObject(['id' => 3, 'name' => 'three']);

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
    public function modifyObjectNames(\Magento\Framework\DataObject $object, $prefix)
    {
        $object->setData('name', $prefix . ' ' . $object->getData('name'));
    }
}
