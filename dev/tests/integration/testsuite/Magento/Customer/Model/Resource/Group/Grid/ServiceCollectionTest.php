<?php
/**
 * \Magento\Customer\Model\Resource\Group\Grid\ServiceCollection
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->collection->addFieldToFilter(array('code'), array(array('NOT LOGGED IN')));
        $this->collection->loadData();
        $items = $this->collection->getItems();
        $this->assertEquals(1, count($items));

        $this->assertEquals('NOT LOGGED IN', $items[0]->getCode());
        $this->assertEquals('0', $items[0]->getId());
        $this->assertEquals('3', $items[0]->getTaxClassId());
    }

    public function testOrArrayFilter()
    {
        $this->collection->addFieldToFilter(array('code', 'code'), array('General', 'Retailer'));
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
        $this->collection->addFieldToFilter('code', array('like' => 'NOT%'));
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
        $this->collection->addFieldToFilter(array(), 'not_array');
    }
}
