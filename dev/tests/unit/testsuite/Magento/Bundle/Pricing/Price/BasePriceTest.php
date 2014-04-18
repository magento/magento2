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

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price as CatalogPrice;

class BasePriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BasePrice
     */
    protected $model;

    /**
     * @var \Magento\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleable;

    /**
     * @var \Magento\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var float
     */
    protected $quantity;

    public function setUp()
    {
        $this->quantity = 1.5;

        $this->saleable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceInfo = $this->getMock('Magento\Pricing\PriceInfoInterface');

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject('Magento\Bundle\Pricing\Price\BasePrice', [
            'salableItem' => $this->saleable,
            'quantity' => $this->quantity
        ]);
    }

    /**
     * @covers \Magento\Bundle\Pricing\Price\BasePrice::applyDiscount
     * @covers \Magento\Bundle\Pricing\Price\BasePrice::getValue
     */
    public function testGetValue()
    {
        $priceValues = [115, 90, 75];
        $tearPriceValue = 15;
        $groupPriceValue = 10;
        $specialPriceValue = 40;
        $result = 45;

        $pricesIncludedInBase = [];
        foreach ($priceValues as $priceValue) {
            $price = $this->getMock('Magento\Pricing\Price\PriceInterface');
            $price->expects($this->atLeastOnce())
                ->method('getValue')
                ->will($this->returnValue($priceValue));
            $pricesIncludedInBase[] = $price;
        }

        $this->priceInfo->expects($this->once())
            ->method('getPricesIncludedInBase')
            ->will($this->returnValue($pricesIncludedInBase));

        $tearPrice = $this->getMock('Magento\Pricing\Price\PriceInterface');
        $tearPrice->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnValue($tearPriceValue));

        $groupPrice = $this->getMock('Magento\Pricing\Price\PriceInterface');
        $groupPrice->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnValue($groupPriceValue));

        $specialPrice = $this->getMock('Magento\Pricing\Price\PriceInterface');
        $specialPrice->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnValue($specialPriceValue));

        $this->priceInfo->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValueMap([
                [CatalogPrice\TierPriceInterface::PRICE_TYPE_TIER, $this->quantity, $tearPrice],
                [CatalogPrice\GroupPriceInterface::PRICE_TYPE_GROUP, $this->quantity, $groupPrice],
                [CatalogPrice\SpecialPriceInterface::PRICE_TYPE_SPECIAL, $this->quantity, $specialPrice],
            ]));

        $this->assertEquals($result, $this->model->getValue());
        $this->assertEquals($result, $this->model->getValue());
    }
}
