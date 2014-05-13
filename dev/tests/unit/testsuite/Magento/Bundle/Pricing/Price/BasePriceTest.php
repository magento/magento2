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
     * @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleable;

    /**
     * @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
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

        $this->priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfoInterface');

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject('Magento\Bundle\Pricing\Price\BasePrice', [
            'saleableItem' => $this->saleable,
            'quantity' => $this->quantity
        ]);
    }

    /**
     * @covers \Magento\Bundle\Pricing\Price\BasePrice::calculateBaseValue
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
            $price = $this->getMock('Magento\Catalog\Pricing\Price\RegularPrice', [], [], '', false);
            $price->expects($this->atLeastOnce())
                ->method('getValue')
                ->will($this->returnValue($priceValue));
            $pricesIncludedInBase[] = $price;
        }

        $this->priceInfo->expects($this->once())
            ->method('getPrices')
            ->will($this->returnValue($pricesIncludedInBase));

        $tearPrice = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');
        $tearPrice->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnValue($tearPriceValue));

        $groupPrice = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');
        $groupPrice->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnValue($groupPriceValue));

        $specialPrice = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');
        $specialPrice->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnValue($specialPriceValue));

        $this->priceInfo->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValueMap([
                [CatalogPrice\TierPrice::PRICE_CODE, $this->quantity, $tearPrice],
                [CatalogPrice\GroupPrice::PRICE_CODE, $this->quantity, $groupPrice],
                [CatalogPrice\SpecialPrice::PRICE_CODE, $this->quantity, $specialPrice],
            ]));

        $this->assertEquals($result, $this->model->getValue());
        $this->assertEquals($result, $this->model->getValue());
    }
}
