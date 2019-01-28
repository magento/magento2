<?php
/**
 * \Magento\Customer\Model\ResourceModel\Group\Grid\ServiceCollection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel\Group\Grid;

class ServiceCollectionTest extends \PHPUnit\Framework\TestCase
{
    /** @var ServiceCollection */
    protected $collection;

    public function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Group\Grid\ServiceCollection::class
        );
    }

    public function testSetOrder()
    {
        $this->collection->setOrder('code', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertSame(4, count($items));

        $this->assertSame('General', $items[0]->getCode());
        $this->assertSame('1', $items[0]->getId());
        $this->assertSame('3', $items[0]->getTaxClassId());

        $this->assertSame('NOT LOGGED IN', $items[1]->getCode());
        $this->assertSame('0', $items[1]->getId());
        $this->assertSame('3', $items[1]->getTaxClassId());

        $this->assertSame('Retailer', $items[2]->getCode());
        $this->assertSame('3', $items[2]->getId());
        $this->assertSame('3', $items[2]->getTaxClassId());

        $this->assertSame('Wholesale', $items[3]->getCode());
        $this->assertSame('2', $items[3]->getId());
        $this->assertSame('3', $items[3]->getTaxClassId());
    }

    public function testArrayFilter()
    {
        $this->collection->addFieldToFilter(['code'], [['NOT LOGGED IN']]);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertSame(1, count($items));

        $this->assertSame('NOT LOGGED IN', $items[0]->getCode());
        $this->assertSame('0', $items[0]->getId());
        $this->assertSame('3', $items[0]->getTaxClassId());
    }

    public function testOrArrayFilter()
    {
        $this->collection->addFieldToFilter(['code', 'code'], ['General', 'Retailer']);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertSame(2, count($items));

        $this->assertSame('General', $items[0]->getCode());
        $this->assertSame('1', $items[0]->getId());
        $this->assertSame('3', $items[0]->getTaxClassId());

        $this->assertSame('Retailer', $items[1]->getCode());
        $this->assertSame('3', $items[1]->getId());
        $this->assertSame('3', $items[1]->getTaxClassId());
    }

    public function testSingleFilter()
    {
        $this->collection->addFieldToFilter('code', 'NOT LOGGED IN');
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertSame(1, count($items));

        $this->assertSame('NOT LOGGED IN', $items[0]->getCode());
        $this->assertSame('0', $items[0]->getId());
        $this->assertSame('3', $items[0]->getTaxClassId());
    }

    public function testSingleLikeFilter()
    {
        $this->collection->addFieldToFilter('code', ['like' => 'NOT%']);
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertSame(1, count($items));

        $this->assertSame('NOT LOGGED IN', $items[0]->getCode());
        $this->assertSame('0', $items[0]->getId());
        $this->assertSame('3', $items[0]->getTaxClassId());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The array of fields failed to pass. The array must include at one field.
     */
    public function testAddToFilterException()
    {
        $this->collection->addFieldToFilter([], 'not_array');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The field array failed to pass. The array must have a matching condition array.
     */
    public function testAddToFilterExceptionArrayNotSymmetric()
    {
        $this->collection->addFieldToFilter(['field2', 'field2'], ['condition1']);
    }
}
