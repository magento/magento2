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

class GroupPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GroupPrice
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

    public function setUp()
    {
        $this->saleable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getPriceInfo', 'getCustomerGroupId', 'getData', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfoInterface');

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject('Magento\Bundle\Pricing\Price\GroupPrice', [
            'saleableItem' => $this->saleable
        ]);
    }

    /**
     * @param float $basePrice
     * @param [] $storedGroupPrice
     * @param float $value
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($basePrice, $storedGroupPrice, $value)
    {
        $customerGroupId = 234;

        $this->saleable->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->will($this->returnValue($customerGroupId));

        $this->saleable->expects($this->once())
            ->method('getData')
            ->with('group_price')
            ->will($this->returnValue($storedGroupPrice));

        if (!empty($storedGroupPrice)) {
            $price = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');
            $this->priceInfo->expects($this->once())
                ->method('getPrice')
                ->with(\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE)
                ->will($this->returnValue($price));
            $price->expects($this->once())
                ->method('getValue')
                ->will($this->returnValue($basePrice));
        }

        $this->assertEquals($value, $this->model->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            ['basePrice' => 100, 'storedGroupPrice' => [['cust_group' => 234, 'website_price' => 40]], 'value' => 60],
            ['basePrice' => 75, 'storedGroupPrice' => [['cust_group' => 234, 'website_price' => 40]], 'value' => 45],
            ['basePrice' => 75, 'storedGroupPrice' => [], 'value' => false],
        );
    }
}
