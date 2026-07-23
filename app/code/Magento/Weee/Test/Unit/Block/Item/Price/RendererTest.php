<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Block\Item\Price;

use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Weee\Block\Item\Price\Renderer;
use Magento\Weee\Helper\Data;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    use MockCreationTrait;
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

        $this->weeeHelper = $this->createPartialMock(
            Data::class,
            [
                'isEnabled',
                'typeOfDisplay',
                'getWeeeTaxInclTax',
                'getRowWeeeTaxInclTax',
                'getBaseRowWeeeTaxInclTax',
                'getBaseWeeeTaxInclTax',
                'getWeeeTaxAppliedRowAmount',
            ]
        );

        $this->priceCurrency = $this->createPartialMock(
            PriceCurrency::class,
            [
                'format',
                'getStore',
                'convertAndRound',
            ]
        );

        $this->item = $this->createPartialMockWithReflection(
            Item::class,
            [
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
                'getQtyOrdered',
                'getCalculationPrice',
                'getPrice',
            ]
        );

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
     */
    #[DataProvider('displayPriceWithWeeeDetailsDataProvider')]
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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
    public function testGetUnitDisplayPriceInclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($price);

        $this->item->expects($this->any())
            ->method('getRowTotalInclTax')
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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
    public function testGetBaseUnitDisplayPriceInclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($price);

        $this->item->expects($this->any())
            ->method('getBaseRowTotalInclTax')
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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
    public function testGetUnitDisplayPriceExclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->priceCurrency->expects($this->once())
            ->method('convertAndRound')
            ->willReturn($price);

        $this->item->expects($this->once())
            ->method('getPrice')
            ->willReturn($price);

        $this->item->expects($this->any())
            ->method('getRowTotal')
            ->willReturn($price);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->with($this->item)
            ->willReturn($weeeTax);

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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
    public function testGetBaseUnitDisplayPriceExclTax(
        int $price,
        int $weeeTax,
        bool $weeeEnabled,
        bool $includeWeee,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($weeeTax);

        $this->item->expects($this->any())
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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
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

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->with($this->item)
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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
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
     */
    #[DataProvider('getDisplayPriceDataProvider')]
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
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
    public function testGetFinalUnitDisplayPriceInclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn($rowTotal);

        $this->item->expects($this->any())
            ->method('getRowTotalInclTax')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceInclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
    public function testGetBaseFinalUnitDisplayPriceInclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->weeeHelper->expects($this->any())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->item->expects($this->once())
            ->method('getBasePriceInclTax')
            ->willReturn($rowTotal);

        $this->item->expects($this->any())
            ->method('getBaseRowTotalInclTax')
            ->willReturn($rowTotal);

        $this->assertEquals($expectedValue, $this->renderer->getBaseFinalUnitDisplayPriceInclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
    public function testGetFinalUnitDisplayPriceExclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->priceCurrency->expects($this->once())
            ->method('convertAndRound')
            ->willReturn($rowTotal);

        $this->item->expects($this->once())
            ->method('getPrice')
            ->willReturn($rowTotal);

        $this->item->expects($this->any())
            ->method('getRowTotal')
            ->willReturn($rowTotal);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->with($this->item)
            ->willReturn($weeeTax);

        $this->assertEquals($expectedValue, $this->renderer->getFinalUnitDisplayPriceExclTax());
    }

    /**
     * @param int $rowTotal
     * @param int $weeeTax
     * @param bool $weeeEnabled
     * @param int $expectedValue
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
    public function testGetBaseFinalUnitDisplayPriceExclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedAmount')
            ->willReturn($weeeTax);

        $this->item->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($weeeTax);

        $this->item->expects($this->any())
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
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
    public function testGetFianlRowDisplayPriceExclTax(
        int $rowTotal,
        int $weeeTax,
        bool $weeeEnabled,
        int $expectedValue
    ) {
        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        $this->weeeHelper->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->with($this->item)
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
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
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
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
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
     */
    #[DataProvider('getFinalDisplayPriceDataProvider')]
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

        $itemMock = $this->createPartialMock(
            OrderItem::class,
            [
                'getRowTotal',
                'getTaxAmount',
                'getDiscountTaxCompensationAmount',
                'getDiscountAmount'
            ]
        );

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

        $itemMock = $this->createPartialMock(
            OrderItem::class,
            [
                'getBaseRowTotal',
                'getBaseTaxAmount',
                'getBaseDiscountTaxCompensationAmount',
                'getBaseDiscountAmount'
            ]
        );

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

    public static function rowTotalFallbackDataProvider(): array
    {
        return [
            'weee_disabled_fallback_to_calc_price_x_qty' => [
                false, // weeeEnabled
                false, // includeWeee (unused when disabled)
                0.0,   // rowTotal
                20.0,  // calculationPrice
                3.0,   // totalQty
                0.0,   // weeeTaxRowAmount
                60.0,  // expected
            ],
            'weee_enabled_exclude_weee_fallback_to_calc_price_x_qty' => [
                true,
                false,
                0.0,
                20.0,
                3.0,
                5.0,
                60.0,
            ],
            'weee_enabled_include_weee_fallback_to_calc_price_x_qty_plus_weee' => [
                true,
                true,
                0.0,
                20.0,
                3.0,
                5.0,
                65.0,
            ],
        ];
    }

    #[DataProvider('rowTotalFallbackDataProvider')]
    public function testGetRowDisplayPriceExclTaxFallsBackToCalculationPriceTimesQty(
        bool $weeeEnabled,
        bool $includeWeee,
        float $rowTotal,
        float $calculationPrice,
        float $totalQty,
        float $weeeTaxRowAmount,
        float $expected
    ): void {
        $item = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getStoreId',
                'getRowTotal',
                'getCalculationPrice',
                'getTotalQty',
            ]
        );

        $item->expects($this->any())
            ->method('getStoreId')
            ->willReturn(self::STORE_ID);

        $item->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);

        $item->expects($this->once())
            ->method('getCalculationPrice')
            ->willReturn($calculationPrice);

        $item->expects($this->once())
            ->method('getTotalQty')
            ->willReturn($totalQty);

        $this->weeeHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn($weeeEnabled);

        if ($weeeEnabled) {
            $this->weeeHelper->expects($this->any())
                ->method('typeOfDisplay')
                ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
                ->willReturn($includeWeee);

            if ($includeWeee) {
                $this->weeeHelper->expects($this->once())
                    ->method('getWeeeTaxAppliedRowAmount')
                    ->with($item)
                    ->willReturn($weeeTaxRowAmount);
            } else {
                $this->weeeHelper->expects($this->never())
                    ->method('getWeeeTaxAppliedRowAmount');
            }
        } else {
            $this->weeeHelper->expects($this->never())
                ->method('getWeeeTaxAppliedRowAmount');
        }

        $this->renderer->setItem($item);
        $this->assertEquals($expected, $this->renderer->getRowDisplayPriceExclTax());
    }

    public function testGetRowDisplayPriceInclTaxSumsDynamicBundleChildren(): void
    {
        $child1 = $this->createPartialMockWithReflection(Item::class, ['getRowTotalInclTax']);
        $child1->expects($this->once())->method('getRowTotalInclTax')->willReturn(40.0);

        $child2 = $this->createPartialMockWithReflection(Item::class, ['getRowTotalInclTax']);
        $child2->expects($this->once())->method('getRowTotalInclTax')->willReturn(50.0);

        $parentItem = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getStoreId',
                'getProductType',
                'getHasChildren',
                'isChildrenCalculated',
                'getChildren',
                'getParentItem',
            ]
        );

        $parentItem->expects($this->any())->method('getStoreId')->willReturn(self::STORE_ID);
        $parentItem->expects($this->any())->method('getProductType')->willReturn(BundleProductType::TYPE_CODE);
        $parentItem->expects($this->any())->method('getHasChildren')->willReturn(true);
        $parentItem->expects($this->any())->method('isChildrenCalculated')->willReturn(true);
        $parentItem->expects($this->any())->method('getChildren')->willReturn([$child1, $child2]);
        $parentItem->expects($this->any())->method('getParentItem')->willReturn(null);

        $this->weeeHelper->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with([WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL], self::ZONE)
            ->willReturn(true);

        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->willReturnCallback(static function ($item) use ($child1, $child2) {
                if ($item === $child1) {
                    return 1.0;
                }
                if ($item === $child2) {
                    return 2.0;
                }
                return 0.0;
            });

        $this->renderer->setItem($parentItem);

        $this->assertEquals(93.0, $this->renderer->getRowDisplayPriceInclTax());
    }

    public function testReconcileSingleCartLineRowInclTaxToGrandTotalMinusShipping(): void
    {
        $itemId = 42;

        $quoteItem = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getStoreId',
                'getId',
                'getQuote',
                'getRowTotalInclTax',
            ]
        );

        $quoteItem->expects($this->any())->method('getStoreId')->willReturn(self::STORE_ID);
        $quoteItem->expects($this->any())->method('getId')->willReturn($itemId);
        $quoteItem->expects($this->any())->method('getRowTotalInclTax')->willReturn(100.0);

        $visibleItem = $this->createPartialMockWithReflection(Item::class, ['getId']);
        $visibleItem->expects($this->any())->method('getId')->willReturn($itemId);

        $address = $this->createPartialMockWithReflection(
            QuoteAddress::class,
            ['getShippingInclTax', 'getShippingAmount', 'getShippingTaxAmount']
        );
        $address->expects($this->any())->method('getShippingInclTax')->willReturn(5.0);

        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['getAllVisibleItems', 'getGrandTotal', 'getIsVirtual', 'getShippingAddress']
        );
        $quote->expects($this->any())->method('getAllVisibleItems')->willReturn([$visibleItem]);
        $quote->expects($this->any())->method('getGrandTotal')->willReturn(105.01);
        $quote->expects($this->any())->method('getIsVirtual')->willReturn(false);
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($address);

        $quoteItem->expects($this->any())->method('getQuote')->willReturn($quote);

        $this->weeeHelper->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with(
                [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL],
                PricingRender::ZONE_CART,
                self::STORE_ID
            )
            ->willReturn(true);
        $this->weeeHelper->expects($this->any())
            ->method('getRowWeeeTaxInclTax')
            ->with($quoteItem)
            ->willReturn(0.0);

        $this->renderer->setZone(PricingRender::ZONE_CART);
        $this->renderer->setItem($quoteItem);

        $this->assertEquals(100.01, $this->renderer->getRowDisplayPriceInclTax());
    }
}
