<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Block\Item\Price;

use Magento\Weee\Model\Tax as WeeeDisplayConfig;

class RendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $renderer;

    /**
     * @var \Magento\Weee\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $weeeHelper;

    /**
     * @var \Magento\Directory\Model\PriceCurrency|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $item;

    const STORE_ID = 'store_id';
    const ZONE = 'zone';

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->weeeHelper = $this->getMockBuilder(\Magento\Weee\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isEnabled',
                'typeOfDisplay',
                'getWeeeTaxInclTax',
                'getRowWeeeTaxInclTax',
                'getBaseRowWeeeTaxInclTax',
                'getBaseWeeeTaxInclTax',
            ])
            ->getMock();

        $this->priceCurrency = $this->getMockBuilder(\Magento\Directory\Model\PriceCurrency::class)
            ->disableOriginalConstructor()
            ->setMethods(['format'])
            ->getMock();

        $this->item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods([
                '__wakeup',
                'getWeeeTaxAppliedAmount',
                'getPriceInclTax',
                'getRowTotalInclTax',
                'getCalculationPrice',
                'getRowTotal',
                'getWeeeTaxAppliedRowAmount',
                'getStoreId',
                'getBaseRowTotalInclTax',
                'getBaseRowTotal',
                'getBaseWeeeTaxAppliedRowAmnt',
                'getBasePrice',
                'getBaseWeeeTaxAppliedAmount',
                'getBaseWeeeTaxInclTax',
                'getBasePriceInclTax',
                'getQtyOrdered'
            ])
            ->getMock();

        $this->item->expects($this->any())
            ->method('getStoreId')
            ->willReturn(self::STORE_ID);

        $this->renderer = $objectManager->getObject(
            \Magento\Weee\Block\Item\Price\Renderer::class,
            [
                'weeeHelper' => $this->weeeHelper,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
        $this->renderer->setItem($this->item);
        $this->renderer->setZone(self::ZONE);
    }

    /**
     * @param bool $isWeeeEnabled
     * @param bool $showWeeeDetails
     * @param bool $hasWeeeAmount
     * @param bool $expectedValue
     * @dataProvider displayPriceWithWeeeDetailsDataProvider
     */
    public function testDisplayPriceWithWeeeDetails(
        $isWeeeEnabled,
        $showWeeeDetails,
        $hasWeeeAmount,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isWeeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with(
                [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL],
                self::ZONE,
                self::STORE_ID
            )->willReturn($showWeeeDetails);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($hasWeeeAmount);

        $this->assertEquals($expectedValue, $this->renderer->displayPriceWithWeeeDetails());
    }

    /**
     * @return array
     */
    public function displayPriceWithWeeeDetailsDataProvider()
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
            'weee_enabled_showdetail_string_zero_false' => [
                'isWeeeEnabled' => true,
                'showWeeeDetails' => true,
                'hasWeeeAmount' => "0.0000",
                'expectedValue' => false,
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
     * @dataProvider getDisplayPriceDataProvider
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
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($priceInclTax);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getUnitDisplayPriceInclTax());
    }

    /**
     * @param $basePriceInclTax
     * @param $baseWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseUnitDisplayPriceInclTax(
        $basePriceInclTax,
        $baseWeeeTaxInclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($baseWeeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($basePriceInclTax);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getBaseUnitDisplayPriceInclTax());
    }

    /**
     * @param $priceExclTax
     * @param $weeeTaxExclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider getDisplayPriceDataProvider
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
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($weeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->willReturn($priceExclTax);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getUnitDisplayPriceExclTax());
    }

    /**
     * @param $basePriceExclTax
     * @param $baseWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseUnitDisplayPriceExclTax(
        $basePriceExclTax,
        $baseWeeeTaxExclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedAmount')
            ->willReturn($baseWeeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($basePriceExclTax);

        $this->item->expects($this->once())
            ->method('getQtyOrdered')
            ->willReturn(1);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getBaseUnitDisplayPriceExclTax());
    }

    /**
     * @param $rowTotal
     * @param $rowWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider getDisplayPriceDataProvider
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
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($rowWeeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getRowDisplayPriceExclTax());
    }

    /**
     * @param $baseRowTotal
     * @param $baseRowWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseRowDisplayPriceExclTax(
        $baseRowTotal,
        $baseRowWeeeTaxExclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($baseRowWeeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getBaseRowDisplayPriceExclTax());
    }

    /**
     * @param $rowTotalInclTax
     * @param $rowWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider getDisplayPriceDataProvider
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
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($rowWeeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn($rowTotalInclTax);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getRowDisplayPriceInclTax());
    }

    /**
     * @param $baseRowTotalInclTax
     * @param $baseRowWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $includeWeee
     * @param $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseRowDisplayPriceInclTax(
        $baseRowTotalInclTax,
        $baseRowWeeeTaxInclTax,
        $weeeEnabled,
        $includeWeee,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($baseRowWeeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotalInclTax')
            ->willReturn($baseRowTotalInclTax);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getBaseRowDisplayPriceInclTax());
    }

    /**
     * @return array
     */
    public function getDisplayPriceDataProvider()
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
            'weee_enabled_include_weee' => [
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => true,
                'include_weee' => true,
                'expected_value' => 110,
            ],
            'weee_enabled_not_include_weee' => [
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
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFinalUnitDisplayPriceInclTax(
        $priceInclTax,
        $weeeTaxInclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($priceInclTax);

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceInclTax());
    }

    /**
     * @param $basePriceInclTax
     * @param $baseWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFinalUnitDisplayPriceInclTax(
        $basePriceInclTax,
        $baseWeeeTaxInclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($baseWeeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($basePriceInclTax);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalUnitDisplayPriceInclTax());
    }

    /**
     * @param $priceExclTax
     * @param $weeeTaxExclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFinalUnitDisplayPriceExclTax(
        $priceExclTax,
        $weeeTaxExclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($weeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->willReturn($priceExclTax);

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceExclTax());
    }

    /**
     * @param $basePriceExclTax
     * @param $baseWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFinalUnitDisplayPriceExclTax(
        $basePriceExclTax,
        $baseWeeeTaxExclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedAmount')
            ->willReturn($baseWeeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($basePriceExclTax);

        $this->item->expects($this->once())
            ->method('getQtyOrdered')
            ->willReturn(1);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalUnitDisplayPriceExclTax());
    }

    /**
     * @param $rowTotal
     * @param $rowWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFianlRowDisplayPriceExclTax(
        $rowTotal,
        $rowWeeeTaxExclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($rowWeeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getFinalRowDisplayPriceExclTax());
    }

    /**
     * @param $baseRowTotal
     * @param $baseRowWeeeTaxExclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFianlRowDisplayPriceExclTax(
        $baseRowTotal,
        $baseRowWeeeTaxExclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($baseRowWeeeTaxExclTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalRowDisplayPriceExclTax());
    }

    /**
     * @param $rowTotalInclTax
     * @param $rowWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFinalRowDisplayPriceInclTax(
        $rowTotalInclTax,
        $rowWeeeTaxInclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($rowWeeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn($rowTotalInclTax);

        $this->assertEquals($expectedValue, $this->renderer->getFinalRowDisplayPriceInclTax());
    }

    /**
     * @param $baseRowTotalInclTax
     * @param $baseRowWeeeTaxInclTax
     * @param $weeeEnabled
     * @param $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFinalRowDisplayPriceInclTax(
        $baseRowTotalInclTax,
        $baseRowWeeeTaxInclTax,
        $weeeEnabled,
        $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($baseRowWeeeTaxInclTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotalInclTax')
            ->willReturn($baseRowTotalInclTax);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalRowDisplayPriceInclTax());
    }

    /**
     * @return array
     */
    public function getFinalDisplayPriceDataProvider()
    {
        $data = [
            'weee_disabled_true' => [
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => false,
                'expected_value' => 100,
            ],
            'weee_enabled_include_weee' => [
                'price' => 100,
                'weee' => 10,
                'weee_enabled' => true,
                'expected_value' => 110,
            ],
        ];
        return $data;
    }

    public function testGetTotalAmount()
    {
        $rowTotal = 100;
        $taxAmount = 10;
        $discountTaxCompensationAmount = 2;
        $discountAmount = 20;
        $weeeAmount = 5;

        $expectedValue = 97;

        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRowTotal',
                    'getTaxAmount',
                    'getDiscountTaxCompensationAmount',
                    'getDiscountAmount',
                    '__wakeup'
                ]
            )
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);

        $itemMock->expects($this->once())
            ->method('getTaxAmount')
            ->willReturn($taxAmount);

        $itemMock->expects($this->once())
            ->method('getDiscountTaxCompensationAmount')
            ->willReturn($discountTaxCompensationAmount);

        $itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn($discountAmount);

        $this->weeeHelper->expects($this->once())
            ->method('getRowWeeeTaxInclTax')
            ->with($itemMock)
            ->willReturn($weeeAmount);

        $this->assertEquals($expectedValue, $this->renderer->getTotalAmount($itemMock));
    }

    public function testGetBaseTotalAmount()
    {
        $baseRowTotal = 100;
        $baseTaxAmount = 10;
        $baseDiscountTaxCompensationAmount = 2;
        $baseDiscountAmount = 20;
        $baseWeeeAmount = 5;

        $expectedValue = $baseRowTotal + $baseTaxAmount + $baseDiscountTaxCompensationAmount -
            $baseDiscountAmount + $baseWeeeAmount;

        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getBaseRowTotal',
                    'getBaseTaxAmount',
                    'getBaseDiscountTaxCompensationAmount',
                    'getBaseDiscountAmount',
                    '__wakeup'
                ]
            )
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal);

        $itemMock->expects($this->once())
            ->method('getBaseTaxAmount')
            ->willReturn($baseTaxAmount);

        $itemMock->expects($this->once())
            ->method('getBaseDiscountTaxCompensationAmount')
            ->willReturn($baseDiscountTaxCompensationAmount);

        $itemMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->willReturn($baseDiscountAmount);

        $this->weeeHelper->expects($this->once())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($itemMock)
            ->willReturn($baseWeeeAmount);

        $this->assertEquals($expectedValue, $this->renderer->getBaseTotalAmount($itemMock));
    }
}
