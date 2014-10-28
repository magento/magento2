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

namespace Magento\Tax\Pricing\Price\Plugin;

/**
 * Class AttributePrice
 *
 */
class AttributePriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $taxHelperMock;

    /** @var \Magento\Tax\Service\V1\TaxCalculationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $calculationServiceMock;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productMock;

    /** @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit_Framework_MockObject_MockObject */
    protected $priceInfoMock;

    /** @var \Magento\Tax\Pricing\Adjustment|\PHPUnit_Framework_MockObject_MockObject */
    protected $adjustmentMock;

    /** @var  \Magento\Tax\Pricing\Price\Plugin\AttributePrice */
    protected $plugin;

    /** @var \Magento\Framework\Object|\PHPUnit_Framework_MockObject_MockObject */
    protected $rateRequestMock;

    /** @var \Magento\ConfigurableProduct\Pricing\Price\AttributePrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributePriceMock;

    /**
     * Test setup
     */
    public function setUp()
    {
        $this->taxHelperMock = $this->getMock(
            'Magento\Tax\Helper\Data',
            ['displayPriceIncludingTax', 'displayBothPrices'],
            [],
            '',
            false,
            false
        );
        $this->calculationServiceMock = $this->getMock(
            'Magento\Tax\Service\V1\TaxCalculationService',
            ['getDefaultCalculatedRate', 'getCalculatedRate'],
            [],
            '',
            false,
            false
        );
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'getTaxClassId', 'getPriceInfo'],
            [],
            '',
            false,
            false
        );
        $this->priceInfoMock = $this->getMock(
            'Magento\Framework\Pricing\PriceInfo\Base',
            ['getAdjustment'],
            [],
            '',
            false,
            false
        );
        $this->adjustmentMock = $this->getMock(
            'Magento\Tax\Pricing\Adjustment',
            ['isIncludedInBasePrice'],
            [],
            '',
            false,
            false
        );
        $this->rateRequestMock = $this->getMock(
            'Magento\Framework\Object',
            ['setProductClassId', '__wakeup'],
            [],
            '',
            false,
            false
        );
        $this->attributePriceMock = $this->getMock(
            'Magento\ConfigurableProduct\Pricing\Price\AttributePrice',
            [],
            [],
            '',
            false,
            false
        );

        $this->plugin = new \Magento\Tax\Pricing\Price\Plugin\AttributePrice(
            $this->taxHelperMock,
            $this->calculationServiceMock
        );


    }

    /**
     * test for method afterPrepareAdjustmentConfig
     */
    public function testAfterPrepareAdjustmentConfig()
    {
        $this->productMock->expects($this->once())
            ->method('getTaxClassId')
            ->will($this->returnValue('tax-class-id'));
        $this->calculationServiceMock->expects($this->once())
            ->method('getDefaultCalculatedRate')
            ->with('tax-class-id', 1)
            ->will($this->returnValue(99.10));
        $this->calculationServiceMock->expects($this->once())
            ->method('getCalculatedRate')
            ->with('tax-class-id', 1)
            ->will($this->returnValue(99.10));
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->priceInfoMock->expects($this->once())
            ->method('getAdjustment')
            ->with($this->equalTo(\Magento\Tax\Pricing\Adjustment::ADJUSTMENT_CODE))
            ->will($this->returnValue($this->adjustmentMock));
        $this->adjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(true));
        $this->taxHelperMock->expects($this->once())
            ->method('displayPriceIncludingTax')
            ->will($this->returnValue(true));
        $this->taxHelperMock->expects($this->once())
            ->method('displayBothPrices')
            ->will($this->returnValue(true));

        $expected = [
            'product' => $this->productMock,
            'defaultTax' => 99.10,
            'currentTax' => 99.10,
            'customerId' => 1,
            'includeTax' => true,
            'showIncludeTax' => true,
            'showBothPrices' => true
        ];

        $this->assertEquals($expected, $this->plugin->afterPrepareAdjustmentConfig($this->attributePriceMock, [
            'product' => $this->productMock,
            'defaultTax' => 0,
            'currentTax' => 0,
            'customerId' => 1,
        ]));
    }
}
