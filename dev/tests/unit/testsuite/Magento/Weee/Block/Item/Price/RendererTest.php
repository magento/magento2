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
namespace Magento\Weee\Block\Item\Price;

use Magento\Framework\Object;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $renderer;

    /**
     * @var \Magento\Weee\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeHelper;

    /**
     * @var \Magento\Directory\Model\PriceCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->weeeHelper = $this->getMockBuilder('\Magento\Weee\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([
                'isEnabled', 'typeOfDisplay', 'getWeeeTaxInclTax', 'getRowWeeeTaxInclTax'
            ])
            ->getMock();

        $this->priceCurrency = $this->getMockBuilder('\Magento\Directory\Model\PriceCurrency')
            ->disableOriginalConstructor()
            ->setMethods(['format'])
            ->getMock();

        $this->item = $this->getMockBuilder('\Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods([
                '__wakeup',
                'getWeeeTaxAppliedAmount',
                'getPriceInclTax',
                'getRowTotalInclTax',
                'getCalculationPrice',
                'getRowTotal',
                'getWeeeTaxAppliedRowAmount',
            ])
            ->getMock();

        $this->renderer = $objectManager->getObject(
            '\Magento\Weee\Block\Item\Price\Renderer',
            [
                'weeeHelper' => $this->weeeHelper,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $this->renderer->setItem($this->item);
    }

    /**
     * @param bool $isWeeeEnabled
     * @param bool #showWeeeDetails
     * @param bool $hasWeeeAmount
     * @dataProvider testDisplayPriceWithWeeeDetailsDataProvider
     */
    public function testDisplayPriceWithWeeeDetails($isWeeeEnabled, $showWeeeDetails, $hasWeeeAmount, $expectedValue)
    {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($isWeeeEnabled));

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR], 'sales', null)
            ->will($this->returnValue($showWeeeDetails));

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue($hasWeeeAmount));

        $this->assertEquals($expectedValue, $this->renderer->displayPriceWithWeeeDetails());
    }

    public function testDisplayPriceWithWeeeDetailsDataProvider()
    {
        $data = [
            'weee_disabled_true_true' => [
                'isWeeeEnabled' => false,
                'showWeeeDetails' => true,
                'hasWeeeAmount' => true,
                'expectedValue' => false,
            ],
            'weee_disabled_true_false' => [
                'isWeeeEnabled' => false,
                'showWeeeDetails' => true,
                'hasWeeeAmount' => false,
                'expectedValue' => false,
            ],
            'weee_disabled_false_true' => [
                'isWeeeEnabled' => false,
                'showWeeeDetails' => false,
                'hasWeeeAmount' => true,
                'expectedValue' => false,
            ],
            'weee_disabled_false_false' => [
                'isWeeeEnabled' => false,
                'showWeeeDetails' => false,
                'hasWeeeAmount' => false,
                'expectedValue' => false,
            ],
            'weee_enabled_showdetail_true' => [
                'isWeeeEnabled' => true,
                'showWeeeDetails' => true,
                'hasWeeeAmount' => true,
                'expectedValue' => true,
            ],
            'weee_enabled_showdetail_false' => [
                'isWeeeEnabled' => true,
                'showWeeeDetails' => true,
                'hasWeeeAmount' => false,
                'expectedValue' => false,
            ],
            'weee_enabled_not_showing_detail_true' => [
                'isWeeeEnabled' => true,
                'showWeeeDetails' => false,
                'hasWeeeAmount' => true,
                'expectedValue' => false,
            ],
            'weee_enabled_not_showing_detail_false' => [
                'isWeeeEnabled' => true,
                'showWeeeDetails' => false,
                'hasWeeeAmount' => false,
                'expectedValue' => false,
            ],
        ];

        return $data;
    }

    /**
     * @param $priceInclTax
     * @param $weeeTaxInclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider testGetDisplayPriceDataProvider
     */
    public function testGetUnitDisplayPriceInclTax(
        $priceInclTax,
        $weeeTaxInclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->will($this->returnValue($weeeTaxInclTax));

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->will($this->returnValue($priceInclTax));

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')
            ->will($this->returnValue($includeWeee));

        $this->assertEquals($expectedValue, $this->renderer->getUnitDisplayPriceInclTax());

    }

    /**
     * @param $priceExclTax
     * @param $weeeTaxExclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider testGetDisplayPriceDataProvider
     */
    public function testGetUnitDisplayPriceExclTax(
        $priceExclTax,
        $weeeTaxExclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue($weeeTaxExclTax));

        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->will($this->returnValue($priceExclTax));

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')
            ->will($this->returnValue($includeWeee));

        $this->assertEquals($expectedValue, $this->renderer->getUnitDisplayPriceExclTax());

    }

    /**
     * @param $rowTotal
     * @param $rowWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider testGetDisplayPriceDataProvider
     */
    public function testGetRowDisplayPriceExclTax(
        $rowTotal,
        $rowWeeeTaxExclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->will($this->returnValue($rowWeeeTaxExclTax));

        $this->item->expects($this->once())
            ->method('getRowTotal')
            ->will($this->returnValue($rowTotal));

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')
            ->will($this->returnValue($includeWeee));

        $this->assertEquals($expectedValue, $this->renderer->getRowDisplayPriceExclTax());

    }

    /**
     * @param $rowTotalInclTax
     * @param $rowWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider testGetDisplayPriceDataProvider
     */
    public function testGetRowDisplayPriceInclTax(
        $rowTotalInclTax,
        $rowWeeeTaxInclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->will($this->returnValue($rowWeeeTaxInclTax));

        $this->item->expects($this->once())
            ->method('getRowTotalInclTax')
            ->will($this->returnValue($rowTotalInclTax));

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], 'sales')
            ->will($this->returnValue($includeWeee));

        $this->assertEquals($expectedValue, $this->renderer->getRowDisplayPriceInclTax());

    }

    public function testGetDisplayPriceDataProvider()
    {
        $data = [
            'weee_disabled_true' => [
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => false,
                'include_weee' => true,
                'expected_value' => 100,
            ],
            'weee_disabled_false' => [
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => false,
                'include_weee' => false,
                'expected_value' => 100,
            ],
            'weee_enabled_include_weee' =>[
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => true,
                'include_weee' => true,
                'expected_value' => 110,
            ],
            'weee_enabled_not_include_weee' =>[
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => true,
                'include_weee' => false,
                'expected_value' => 100,
            ],
        ];
        return $data;
    }

    /**
     * @param $priceInclTax
     * @param $weeeTaxInclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider testGetFinalDisplayPriceDataProvider
     */
    public function testGetFinalUnitDisplayPriceInclTax(
        $priceInclTax,
        $weeeTaxInclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->will($this->returnValue($weeeTaxInclTax));

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->will($this->returnValue($priceInclTax));

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceInclTax());

    }

    /**
     * @param $priceExclTax
     * @param $weeeTaxExclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider testGetFinalDisplayPriceDataProvider
     */
    public function testGetFinalUnitDisplayPriceExclTax(
        $priceExclTax,
        $weeeTaxExclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue($weeeTaxExclTax));

        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->will($this->returnValue($priceExclTax));

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceExclTax());

    }

    /**
     * @param $rowTotal
     * @param $rowWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider testGetFinalDisplayPriceDataProvider
     */
    public function testGetFianlRowDisplayPriceExclTax(
        $rowTotal,
        $rowWeeeTaxExclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->will($this->returnValue($rowWeeeTaxExclTax));

        $this->item->expects($this->once())
            ->method('getRowTotal')
            ->will($this->returnValue($rowTotal));

        $this->assertEquals($expectedValue, $this->renderer->getFinalRowDisplayPriceExclTax());

    }

    /**
     * @param $rowTotalInclTax
     * @param $rowWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider testGetFinalDisplayPriceDataProvider
     */
    public function testGetFinalRowDisplayPriceInclTax(
        $rowTotalInclTax,
        $rowWeeeTaxInclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($weeeEnabled));

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->will($this->returnValue($rowWeeeTaxInclTax));

        $this->item->expects($this->once())
            ->method('getRowTotalInclTax')
            ->will($this->returnValue($rowTotalInclTax));

        $this->assertEquals($expectedValue, $this->renderer->getFinalRowDisplayPriceInclTax());

    }

    public function testGetFinalDisplayPriceDataProvider()
    {
        $data = [
            'weee_disabled_true' => [
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => false,
                'expected_value' => 100,
            ],
            'weee_enabled_include_weee' =>[
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => true,
                'expected_value' => 110,
            ],
        ];
        return $data;
    }

    public function testFormatPrice()
    {
        $price = 10;
        $formattedPrice ="$10.00";
        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with($price)
            ->will($this->returnValue($formattedPrice));

        $this->assertEquals($formattedPrice, $this->renderer->formatPrice($price));
    }
}
