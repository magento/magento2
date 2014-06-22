<?php
/**
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

namespace Magento\Catalog\Model\Product;

class PriceModifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier
     */
    protected $priceModifier;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var array
     */
    protected $prices = array();

    protected function setUp()
    {
        $this->productMock =
            $this->getMock('Magento\Catalog\Model\Product',
                array('getData', 'setData', '__wakeup'), array(), '', false);
        $this->priceModifier = new \Magento\Catalog\Model\Product\PriceModifier();
        $this->prices = array(
            0 => array(
                'all_groups' => 0,
                'cust_group' => 1,
                'price_qty' => 15,
                'website_id' => 1
            ),
            1 => array(
                'all_groups' => 1,
                'cust_group' => 0,
                'price_qty' => 10,
                'website_id' => 1
            )
        );
    }

    public function testSuccessfullyRemoveGroupPriceSpecifiedForOneGroup()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue($this->prices));
        $expectedPrices = array(1 => $this->prices[1]);
        $this->productMock->expects($this->once())->method('setData')->with('group_price', $expectedPrices);
        $this->priceModifier->removeGroupPrice($this->productMock, 1, 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedMessage This product doesn't have group price
     */
    public function testRemoveWhenGroupPricesNotExists()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue(array()));
        $this->productMock->expects($this->never())->method('setData');
        $this->priceModifier->removeGroupPrice($this->productMock, 1, 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedMessage For current  customerGroupId = '10' any group price exist'.
     */
    public function testRemoveGroupPriceForNonExistingCustomerGroup()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue($this->prices));
        $this->productMock->expects($this->never())->method('setData');
        $this->priceModifier->removeGroupPrice($this->productMock, 10, 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedMessage This product doesn't have tier price
     */
    public function testRemoveWhenTierPricesNotExists()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue(array()));
        $this->productMock->expects($this->never())->method('setData');
        $this->priceModifier->removeTierPrice($this->productMock, 1, 3, 1);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedMessage For current  customerGroupId = '10' with 'qty' = 15 any tier price exist'.
     */
    public function testRemoveTierPriceForNonExistingCustomerGroup()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue($this->prices));
        $this->productMock->expects($this->never())->method('setData');
        $this->priceModifier->removeTierPrice($this->productMock, 10, 15, 1);
    }

    public function testSuccessfullyRemoveTierPriceSpecifiedForAllGroups()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue($this->prices));
        $expectedPrices = array($this->prices[0]);
        $this->productMock->expects($this->once())->method('setData')->with('tier_price', $expectedPrices);
        $this->priceModifier->removeTierPrice($this->productMock, 'all', 10, 1);
    }

    public function testSuccessfullyRemoveTierPriceSpecifiedForSpecificGroups()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->will($this->returnValue($this->prices));
        $expectedPrices = array(1 => $this->prices[1]);
        $this->productMock->expects($this->once())->method('setData')->with('tier_price', $expectedPrices);
        $this->priceModifier->removeTierPrice($this->productMock, 1, 15, 1);
    }
}
