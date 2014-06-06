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

class SpecialPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SpecialPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleable;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    public function setUp()
    {
        $this->saleable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDate = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject('Magento\Bundle\Pricing\Price\SpecialPrice', [
            'saleableItem' => $this->saleable,
            'localeDate' => $this->localeDate
        ]);
    }

    /**
     * @param $regularPrice
     * @param $specialPrice
     * @param $isScopeDateInInterval
     * @param $value
     * @param $percent
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($regularPrice, $specialPrice, $isScopeDateInInterval, $value, $percent)
    {
        $specialFromDate =  'some date from';
        $specialToDate =  'some date to';

        $this->saleable->expects($this->once())
            ->method('getSpecialPrice')
            ->will($this->returnValue($specialPrice));

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->saleable->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));
        $this->saleable->expects($this->once())
            ->method('getSpecialFromDate')
            ->will($this->returnValue($specialFromDate));
        $this->saleable->expects($this->once())
            ->method('getSpecialToDate')
            ->will($this->returnValue($specialToDate));

        $this->localeDate->expects($this->once())
            ->method('isScopeDateInInterval')
            ->with($store, $specialFromDate, $specialToDate)
            ->will($this->returnValue($isScopeDateInInterval));

        if ($isScopeDateInInterval) {
            $price = $this->getMock('Magento\Framework\Pricing\Price\PriceInterface');
            $this->priceInfo->expects($this->once())
                ->method('getPrice')
                ->with(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
                ->will($this->returnValue($price));
            $price->expects($this->once())
                ->method('getValue')
                ->will($this->returnValue($regularPrice));
        }

        $this->assertEquals($value, $this->model->getValue());

        //check that the second call will get data from cache the same as in first call
        $this->assertEquals($value, $this->model->getValue());
        $this->assertEquals($percent, $this->model->getDiscountPercent());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            ['regularPrice' => 100, 'specialPrice' => 40, 'isScopeDateInInterval' => true,  'value' => 40,
                'percent' => 40],
            ['regularPrice' => 75,  'specialPrice' => 40, 'isScopeDateInInterval' => true,  'value' => 30,
                'percent' => 40],
            ['regularPrice' => 75,  'specialPrice' => 40, 'isScopeDateInInterval' => false, 'value' => false,
                'percent' => null],
        );
    }
}
