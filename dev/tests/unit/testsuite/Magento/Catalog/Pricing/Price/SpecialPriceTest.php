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

namespace Magento\Catalog\Pricing\Price;

class SpecialPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param bool $isValidInterval
     * @param float $specialPrice
     * @param float|bool $expected
     *
     * @dataProvider specialPriceDataProvider
     */
    public function testGetValue($isValidInterval, $specialPrice, $expected)
    {
        $specialPriceModel = $this->objectManager->getObject(
            'Magento\Catalog\Pricing\Price\SpecialPrice',
            [
                'saleableItem' => $this->prepareSaleableItem($specialPrice),
                'localeDate'  => $this->prepareLocaleDate($isValidInterval)
            ]
        );

        $this->assertSame($expected, $specialPriceModel->getValue());
    }

    /**
     * @param float $specialPrice
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected function prepareSaleableItem($specialPrice)
    {
        $saleableItemMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getSpecialPrice', 'getPriceInfo', 'getStore', '__wakeup'],
            [],
            '',
            false
        );

        $saleableItemMock->expects($this->any())
            ->method('getSpecialPrice')
            ->will($this->returnValue($specialPrice));

        $priceInfo = $this->getMockBuilder(
            'Magento\Framework\Pricing\PriceInfoInterface'
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $priceInfo->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue([]));

        $saleableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));

        return $saleableItemMock;
    }

    /**
     * @param bool $isValidInterval
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected function prepareLocaleDate($isValidInterval)
    {
        $localeDate = $this->getMockBuilder(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $localeDate->expects($this->any())
            ->method('isScopeDateInInterval')
            ->will($this->returnValue($isValidInterval));

        return $localeDate;
    }

    /**
     * @return array
     */
    public function specialPriceDataProvider()
    {
        return [
            'validInterval' => [
                'is_valid_date' => true,
                'special_price' => 50.15,
                'expected'      => 50.15,
            ],
            'validZeroValue' => [
                'is_valid_date' => true,
                'special_price' => 0.,
                'expected'      => 0.,
            ],
            'invalidInterval' => [
                'is_valid_date' => false,
                'special_price' => 20.,
                'expected'      => false,
            ]
        ];
    }
}
