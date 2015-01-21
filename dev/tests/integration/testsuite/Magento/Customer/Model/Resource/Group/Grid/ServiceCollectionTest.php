<?php
/**
 * \Magento\Customer\Model\Resource\Group\Grid\ServiceCollection
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource\Group\Grid;

class ServiceCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceCollection */
    protected $collection;

    public function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Resource\Group\Grid\ServiceCollection'
        );
    }

    public function testSetOrder()
    {
        $this->collection->setOrder('code', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertEquals(4, count($items));

        $this->assertEquals('General', $items[0]->getCode());
        $this->assertEquals('1', $items[0]->getId());
        $this->assertEquals('3', $items[0]->getTaxClassId());

        $this->assertEquals('NOT LOGGED IN', $items[1]->getCode());
        $this->assertEquals('0', $items[1]->getId());
        $this->assertEquals('3', $items[1]->getTaxClassId());

        $this->assertEquals('Retailer', $items[2]->getCode());
        $this->assertEquals('3', $items[2]->getId());
        $this->assertEquals('3', $items[2]->getTaxClassId());

        $this->assertEquals('Wholesale', $items[3]->getCode());
        $this->assertEquals('2', $items[3]->getId());
        $this->assertEquals('3', $items[3]->getTaxClassId());
    }

    public function testArrayFilter()
    {
        $this->collection->addFieldToFilter(['code'], [['NOT LOGGED IN']]);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertEquals(1, count($items));

        $this->assertEquals('NOT LOGGED IN', $items[0]->getCode());
        $this->assertEquals('0', $items[0]->getId());
        $this->assertEquals('3', $items[0]->getTaxClassId());
    }

    public function testOrArrayFilter()
    {
        $this->collection->addFieldToFilter(['code', 'code'], ['General', 'Retailer']);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertEquals(2, count($items));

        $this->assertEquals('General', $items[0]->getCode());
        $this->assertEquals('1', $items[0]->getId());
        $this->assertEquals('3', $items[0]->getTaxClassId());

        $this->assertEquals('Retailer', $items[1]->getCode());
        $this->assertEquals('3', $items[1]->getId());
        $this->assertEquals('3', $items[1]->getTaxClassId());
    }

    public function testSingleFilter()
    {
        $this->collection->addFieldToFilter('code', 'NOT LOGGED IN');
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertEquals(1, count($items));

        $this->assertEquals('NOT LOGGED IN', $items[0]->getCode());
        $this->assertEquals('0', $items[0]->getId());
        $this->assertEquals('3', $items[0]->getTaxClassId());
    }

    public function testSingleLikeFilter()
    {
        $this->collection->addFieldToFilter('code', ['like' => 'NOT%']);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertEquals(1, count($items));

        $this->assertEquals('NOT LOGGED IN', $items[0]->getCode());
        $this->assertEquals('0', $items[0]->getId());
        $this->assertEquals('3', $items[0]->getTaxClassId());
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage When passing in a field array there must be a matching condition array.
     */
    public function testAddToFilterException()
    {
        $this->collection->addFieldToFilter([], 'not_array');
    }
}
