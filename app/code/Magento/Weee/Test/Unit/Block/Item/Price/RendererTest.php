<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Block\Item\Price;

use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Weee\Block\Item\Price\Renderer;
use Magento\Weee\Helper\Data;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Data|MockObject
     */
    protected $weeeHelper;

    /**
     * @var PriceCurrency|MockObject
     */
    protected $priceCurrency;

    /**
     * @var Item|MockObject
     */
    protected $item;

    private const STORE_ID = 'store_id';
    private const ZONE = 'zone';

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->weeeHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isEnabled',
                'typeOfDisplay',
                'getWeeeTaxInclTax',
                'getRowWeeeTaxInclTax',
                'getBaseRowWeeeTaxInclTax',
                'getBaseWeeeTaxInclTax',
            ])
            ->getMock();

        $this->priceCurrency = $this->getMockBuilder(PriceCurrency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['format'])
            ->getMock();

        $this->item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getWeeeTaxAppliedAmount',
                'getPriceInclTax',
                'getRowTotal',
                'getRowTotalInclTax',
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
            ->onlyMethods(['getCalculationPrice'])
            ->getMock();

        $this->item->expects($this->any())
            ->method('getStoreId')
            ->willReturn(self::STORE_ID);

        $this->renderer = $objectManager->getObject(
            Renderer::class,
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
    public static function displayPriceWithWeeeDetailsDataProvider()
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
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetUnitDisplayPriceInclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getUnitDisplayPriceInclTax());
    }

    /**
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseUnitDisplayPriceInclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getBaseUnitDisplayPriceInclTax());
    }

    /**
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetUnitDisplayPriceExclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getUnitDisplayPriceExclTax());
    }

    /**
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseUnitDisplayPriceExclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($price);

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
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetRowDisplayPriceExclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getRowDisplayPriceExclTax());
    }

    /**
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseRowDisplayPriceExclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getBaseRowDisplayPriceExclTax());
    }

    /**
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetRowDisplayPriceInclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getRowDisplayPriceInclTax());
    }

    /**
     * @param int $price
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param bool $includeWeee
     * @param int $expectedValue
     * @dataProvider getDisplayPriceDataProvider
     */
    public function testGetBaseRowDisplayPriceInclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotalInclTax')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn($includeWeee);

        $this->assertEquals($expectedValue, $this->renderer->getBaseRowDisplayPriceInclTax());
    }

    /**
     * @return array
     */
    public static function getDisplayPriceDataProvider()
    {
        $data = [
            'weee_disabled_true' => [
                'price' => 100,
                'weeeTax' => 10,
                'weeeEnabled' => false,
                'includeWeee' => true,
                'expectedValue' => 100,
            ],
            'weee_disabled_false' => [
                'price' => 100,
                'weeeTax' => 10,
                'weeeEnabled' => false,
                'includeWeee' => false,
                'expectedValue' => 100,
            ],
            'weee_enabled_include_weee' => [
                'price' => 100,
                'weeeTax' => 10,
                'weeeEnabled' => true,
                'includeWeee' => true,
                'expectedValue' => 110,
            ],
            'weee_enabled_not_include_weee' => [
                'price' => 100,
                'weeeTax' => 10,
                'weeeEnabled' => true,
                'includeWeee' => false,
                'expectedValue' => 100,
            ],
        ];
        return $data;
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFinalUnitDisplayPriceInclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceInclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFinalUnitDisplayPriceInclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalUnitDisplayPriceInclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFinalUnitDisplayPriceExclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceExclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFinalUnitDisplayPriceExclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($rowTotal);

        $this->item->expects($this->once())
            ->method('getQtyOrdered')
            ->willReturn(1);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalUnitDisplayPriceExclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFianlRowDisplayPriceExclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getFinalRowDisplayPriceExclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFianlRowDisplayPriceExclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalRowDisplayPriceExclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetFinalRowDisplayPriceInclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getFinalRowDisplayPriceInclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     * @dataProvider getFinalDisplayPriceDataProvider
     */
    public function testGetBaseFinalRowDisplayPriceInclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBaseRowTotalInclTax')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalRowDisplayPriceInclTax());
    }

    /**
     * @return array
     */
    public static function getFinalDisplayPriceDataProvider()
    {
        $data = [
            'weee_disabled_true' => [
                'rowTotal' => 100,
                'weeeTax' => 10,
                'weeeEnabled' => false,
                'expectedValue' => 100,
            ],
            'weee_enabled_include_weee' => [
                'rowTotal' => 100,
                'weeeTax' => 10,
                'weeeEnabled' => true,
                'expectedValue' => 110,
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
            ->onlyMethods(
                [
                    'getRowTotal',
                    'getTaxAmount',
                    'getDiscountTaxCompensationAmount',
                    'getDiscountAmount'
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
            ->onlyMethods(
                [
                    'getBaseRowTotal',
                    'getBaseTaxAmount',
                    'getBaseDiscountTaxCompensationAmount',
                    'getBaseDiscountAmount'
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
