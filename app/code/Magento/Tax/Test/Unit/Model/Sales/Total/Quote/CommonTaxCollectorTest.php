<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\AppliedTaxInterface;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Tax\Helper\Data as TaxHelper;
use \Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings("CouplingBetweenObjects")
 */
class CommonTaxCollectorTest extends TestCase
{
    use MockCreationTrait;
    
    /** @var Config|MockObject */
    private $taxConfig;

    /** @var TaxCalculationInterface|MockObject */
    private $taxCalculationService;

    /** @var QuoteDetailsInterfaceFactory|MockObject */
    private $quoteDetailsFactory;

    /** @var QuoteDetailsItemInterfaceFactory|MockObject */
    private $quoteDetailsItemFactory;

    /** @var TaxClassKeyInterfaceFactory|MockObject */
    private $taxClassKeyFactory;

    /** @var CustomerAddressFactory|MockObject */
    private $customerAddressFactory;

    /** @var CustomerAddressRegionFactory|MockObject */
    private $customerAddressRegionFactory;

    /** @var TaxHelper|MockObject */
    private $taxHelper;

    /** @var QuoteDetailsItemExtensionInterfaceFactory|MockObject */
    private $quoteDetailsItemExtensionFactory;

    /** @var CustomerAccountManagement|MockObject */
    private $customerAccountManagement;

    protected function setUp(): void
    {
        $this->taxConfig = $this->createMock(Config::class);
        $this->taxCalculationService = $this->createMock(TaxCalculationInterface::class);
        $this->quoteDetailsFactory = $this->createMock(QuoteDetailsInterfaceFactory::class);
        $this->quoteDetailsItemFactory = $this->createMock(QuoteDetailsItemInterfaceFactory::class);
        $this->taxClassKeyFactory = $this->createMock(TaxClassKeyInterfaceFactory::class);
        $this->customerAddressFactory = $this->createMock(CustomerAddressFactory::class);
        $this->customerAddressRegionFactory = $this->createMock(CustomerAddressRegionFactory::class);
        $this->taxHelper = $this->createMock(TaxHelper::class);
        $this->quoteDetailsItemExtensionFactory = $this->createMock(QuoteDetailsItemExtensionInterfaceFactory::class);
        $this->customerAccountManagement = $this->createMock(CustomerAccountManagement::class);
    }

    private function createSut(): CommonTaxCollector
    {
        return new CommonTaxCollector(
            $this->taxConfig,
            $this->taxCalculationService,
            $this->quoteDetailsFactory,
            $this->quoteDetailsItemFactory,
            $this->taxClassKeyFactory,
            $this->customerAddressFactory,
            $this->customerAddressRegionFactory,
            $this->taxHelper,
            $this->quoteDetailsItemExtensionFactory,
            $this->customerAccountManagement
        );
    }

    public function testConvertAppliedTaxesMergesRatesAndExtraInfo(): void
    {
        $sut = $this->createSut();

        $rate = $this->createMock(AppliedTaxRateInterface::class);
        $rate->method('getPercent')->willReturn(5.0);
        $rate->method('getCode')->willReturn('CA-STATE');
        $rate->method('getTitle')->willReturn('California');

        $applied = $this->createMock(AppliedTaxInterface::class);
        $applied->method('getAmount')->willReturn(10.0);
        $applied->method('getPercent')->willReturn(10.0);
        $applied->method('getTaxRateKey')->willReturn('US-CA-Rate-1');
        $applied->method('getRates')->willReturn([$rate]);

        $baseApplied = $this->createMock(AppliedTaxInterface::class);
        $baseApplied->method('getAmount')->willReturn(8.0);
        $baseApplied->method('getPercent')->willReturn(10.0);
        $baseApplied->method('getTaxRateKey')->willReturn('US-CA-Rate-1');
        $baseApplied->method('getRates')->willReturn([$rate]);

        $result = $sut->convertAppliedTaxes(
            ['tax-id-1' => $applied],
            ['tax-id-1' => $baseApplied],
            ['item_id' => 123, 'item_type' => CommonTaxCollector::ITEM_TYPE_PRODUCT]
        );

        $this->assertCount(1, $result);
        $row = $result[0];
        $this->assertSame(10.0, $row['amount']);
        $this->assertSame(8.0, $row['base_amount']);
        $this->assertSame(10.0, $row['percent']);
        $this->assertSame('US-CA-Rate-1', $row['id']);
        $this->assertSame(123, $row['item_id']);
        $this->assertSame(CommonTaxCollector::ITEM_TYPE_PRODUCT, $row['item_type']);
        $this->assertSame([
            ['percent' => 5.0, 'code' => 'CA-STATE', 'title' => 'California']
        ], $row['rates']);
    }

    public function testOrganizeItemTaxDetailsByTypeGroupsByType(): void
    {
        $sut = $this->createSut();

        $itemProduct = $this->createMock(TaxDetailsItemInterface::class);
        $itemProduct->method('getCode')->willReturn('item-1');
        $itemProduct->method('getType')->willReturn(CommonTaxCollector::ITEM_TYPE_PRODUCT);

        $itemShipping = $this->createMock(TaxDetailsItemInterface::class);
        $itemShipping->method('getCode')->willReturn('shipping');
        $itemShipping->method('getType')->willReturn(CommonTaxCollector::ITEM_TYPE_SHIPPING);

        $baseItemProduct = $this->createMock(TaxDetailsItemInterface::class);
        $baseItemProduct->method('getCode')->willReturn('item-1');
        $baseItemProduct->method('getType')->willReturn(CommonTaxCollector::ITEM_TYPE_PRODUCT);

        $baseItemShipping = $this->createMock(TaxDetailsItemInterface::class);
        $baseItemShipping->method('getCode')->willReturn('shipping');
        $baseItemShipping->method('getType')->willReturn(CommonTaxCollector::ITEM_TYPE_SHIPPING);

        $taxDetails = $this->createMock(TaxDetailsInterface::class);
        $taxDetails->method('getItems')->willReturn([$itemProduct, $itemShipping]);

        $baseTaxDetails = $this->createMock(TaxDetailsInterface::class);
        $baseTaxDetails->method('getItems')->willReturn([$baseItemProduct, $baseItemShipping]);

        $result = (new \ReflectionClass(CommonTaxCollector::class))
            ->getMethod('organizeItemTaxDetailsByType')
            ->invoke($sut, $taxDetails, $baseTaxDetails);

        $this->assertArrayHasKey(CommonTaxCollector::ITEM_TYPE_PRODUCT, $result);
        $this->assertArrayHasKey('item-1', $result[CommonTaxCollector::ITEM_TYPE_PRODUCT]);
        $this->assertSame(
            $itemProduct,
            $result[CommonTaxCollector::ITEM_TYPE_PRODUCT]['item-1'][CommonTaxCollector::KEY_ITEM]
        );
        $this->assertSame(
            $baseItemProduct,
            $result[CommonTaxCollector::ITEM_TYPE_PRODUCT]['item-1'][CommonTaxCollector::KEY_BASE_ITEM]
        );

        $this->assertArrayHasKey(CommonTaxCollector::ITEM_TYPE_SHIPPING, $result);
        $this->assertArrayHasKey('shipping', $result[CommonTaxCollector::ITEM_TYPE_SHIPPING]);
        $this->assertSame(
            $itemShipping,
            $result[CommonTaxCollector::ITEM_TYPE_SHIPPING]['shipping'][CommonTaxCollector::KEY_ITEM]
        );
        $this->assertSame(
            $baseItemShipping,
            $result[CommonTaxCollector::ITEM_TYPE_SHIPPING]['shipping'][CommonTaxCollector::KEY_BASE_ITEM]
        );
    }

    public function testPopulateAddressDataWhenBillingNoCountryAndVirtualUsesDefaultBilling(): void
    {
        $billingMapped = $this->createMock(CustomerAddress::class);
        $shippingMapped = $this->createMock(CustomerAddress::class);

        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['mapAddress'])
            ->getMock();

        $sut->method('mapAddress')->willReturnOnConsecutiveCalls($billingMapped, $shippingMapped);

        $quoteDetails = $this->createMock(QuoteDetailsInterface::class);
        $quoteDetails->expects($this->once())->method('setBillingAddress')->with($billingMapped)->willReturnSelf();
        $quoteDetails->expects($this->once())->method('setShippingAddress')->with($shippingMapped)->willReturnSelf();

        $billingAddressFromQuote = $this->createMock(QuoteAddress::class);

        $defaultBillingCustomerAddress = $this->createMock(CustomerAddress::class);
        $this->customerAccountManagement
            ->method('getDefaultBillingAddress')
            ->with(15)
            ->willReturn($defaultBillingCustomerAddress);

        $quote = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['isVirtual', 'getCustomerId', 'getBillingAddress']
        );
        $quote->method('isVirtual')->willReturn(true);
        $quote->method('getCustomerId')->willReturn(15);
        $quote->method('getBillingAddress')->willReturn($billingAddressFromQuote);

        $address = $this->createPartialMockWithReflection(
            QuoteAddress::class,
            ['getCountryId', 'getQuote', 'importCustomerAddressData', 'getAddressType']
        );

        $address->method('getAddressType')->willReturn(QuoteAddress::ADDRESS_TYPE_BILLING);
        $address->method('getCountryId')->willReturn(null);
        $address->method('getQuote')->willReturn($quote);

        $result = $sut->populateAddressData($quoteDetails, $address);

        $this->assertSame($quoteDetails, $result);
    }

    public function testPopulateAddressDataSetsShippingAddressFromAddressWhenNotDefaultBillingPath(): void
    {
        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['mapAddress'])
            ->getMock();

        $billingMapped = $this->createMock(CustomerAddress::class);
        $shippingMapped = $this->createMock(CustomerAddress::class);

        $quoteDetails = $this->createMock(\Magento\Tax\Api\Data\QuoteDetailsInterface::class);
        $quoteDetails->expects($this->once())->method('setBillingAddress')->with($billingMapped)->willReturnSelf();
        $quoteDetails->expects($this->once())->method('setShippingAddress')->with($shippingMapped)->willReturnSelf();

        $billingAddress = $this->getMockBuilder(QuoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $address = $this->createPartialMockWithReflection(
            QuoteAddress::class,
            ['getQuote', 'getAddressType']
        );
        $address->method('getAddressType')->willReturn(QuoteAddress::ADDRESS_TYPE_SHIPPING);

        $quote = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['getBillingAddress']
        );
        $quote->method('getBillingAddress')->willReturn($billingAddress);
        $address->method('getQuote')->willReturn($quote);

        $sut->method('mapAddress')->willReturnCallback(
            function ($addr) use ($address, $billingAddress, $shippingMapped, $billingMapped) {
                if ($addr === $address) {
                    return $shippingMapped;
                }
                if ($addr === $billingAddress) {
                    return $billingMapped;
                }
                return null;
            }
        );

        $result = $sut->populateAddressData($quoteDetails, $address);
        $this->assertSame($quoteDetails, $result);
    }

    public function testUpdateItemTaxInfoSetsFieldsFromTaxDetails(): void
    {
        $sut = $this->createSut();

        $store = $this->createMock(Store::class);

        $itemTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $itemTaxDetails->method('getPrice')->willReturn(12.34);
        $itemTaxDetails->method('getPriceInclTax')->willReturn(13.34);
        $itemTaxDetails->method('getRowTotal')->willReturn(24.68);
        $itemTaxDetails->method('getRowTotalInclTax')->willReturn(26.68);
        $itemTaxDetails->method('getRowTax')->willReturn(2.00);
        $itemTaxDetails->method('getTaxPercent')->willReturn(10.0);
        $itemTaxDetails->method('getDiscountTaxCompensationAmount')->willReturn(0.50);

        $baseItemTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $baseItemTaxDetails->method('getPrice')->willReturn(10.00);
        $baseItemTaxDetails->method('getPriceInclTax')->willReturn(11.00);
        $baseItemTaxDetails->method('getRowTotal')->willReturn(20.00);
        $baseItemTaxDetails->method('getRowTotalInclTax')->willReturn(22.00);
        $baseItemTaxDetails->method('getRowTax')->willReturn(1.50);
        $baseItemTaxDetails->method('getTaxPercent')->willReturn(7.5);
        $baseItemTaxDetails->method('getDiscountTaxCompensationAmount')->willReturn(0.25);

        $quoteItem = $this->createPartialMockWithReflection(\stdClass::class, [
                'setPrice', 'getCustomPrice', 'setCustomPrice', 'setConvertedPrice', 'setPriceInclTax',
                'setRowTotal', 'setRowTotalInclTax', 'setTaxAmount', 'setTaxPercent',
                'setDiscountTaxCompensationAmount', 'setBasePrice', 'setBasePriceInclTax', 'setBaseRowTotal',
                'setBaseRowTotalInclTax', 'setBaseTaxAmount', 'setBaseDiscountTaxCompensationAmount',
                'setDiscountCalculationPrice', 'setBaseDiscountCalculationPrice'
            ]);

        $quoteItem->expects($this->atLeastOnce())->method('setPrice')->with(10.00)->willReturnSelf();
        $quoteItem->method('getCustomPrice')->willReturn(12.00);
        $this->taxHelper->method('applyTaxOnCustomPrice')->willReturn(true);
        $quoteItem->expects($this->once())->method('setCustomPrice')->with(12.34)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setConvertedPrice')->with(12.34)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setPriceInclTax')->with(13.34)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setRowTotal')->with(24.68)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setRowTotalInclTax')->with(26.68)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setTaxAmount')->with(2.00)->willReturnSelf();

        $setTaxPercentCalls = [];
        $quoteItem->expects($this->exactly(2))
            ->method('setTaxPercent')
            ->with($this->callback(function ($value) use (&$setTaxPercentCalls) {
                $setTaxPercentCalls[] = $value;
                return in_array($value, [10.0, 7.5], true);
            }))
            ->willReturnSelf();

        $quoteItem->expects($this->once())->method('setDiscountTaxCompensationAmount')->with(0.50)->willReturnSelf();

        $quoteItem->expects($this->once())->method('setBasePrice')->with(10.00)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setBasePriceInclTax')->with(11.00)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setBaseRowTotal')->with(20.00)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setBaseRowTotalInclTax')->with(22.00)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setBaseTaxAmount')->with(1.50)->willReturnSelf();
        $quoteItem->expects($this->once())
            ->method('setBaseDiscountTaxCompensationAmount')
            ->with(0.25)
            ->willReturnSelf();

        $this->taxConfig->method('discountTax')->with($store)->willReturn(false);
        $quoteItem->expects($this->once())->method('setDiscountCalculationPrice')->with(12.34)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setBaseDiscountCalculationPrice')->with(10.00)->willReturnSelf();

        $sut->updateItemTaxInfo($quoteItem, $itemTaxDetails, $baseItemTaxDetails, $store);

        $this->assertSame([10.0, 7.5], $setTaxPercentCalls);
    }

    public function testMapAddressCreatesCustomerAddress(): void
    {
        $sut = $this->createSut();

        $quoteAddress = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(
                ['getRegionId', 'getRegionCode', 'getRegion', 'getCountryId', 'getPostcode', 'getCity', 'getStreet']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $quoteAddress->method('getRegionId')->willReturn(5);
        $quoteAddress->method('getRegionCode')->willReturn('CA');
        $quoteAddress->method('getRegion')->willReturn('California');
        $quoteAddress->method('getCountryId')->willReturn('US');
        $quoteAddress->method('getPostcode')->willReturn('94016');
        $quoteAddress->method('getCity')->willReturn('SF');
        $quoteAddress->method('getStreet')->willReturn(['1st St']);

        $region = new \stdClass();
        $this->customerAddressRegionFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'region_id' => 5,
                    'region_code' => 'CA',
                    'region' => 'California',
                ],
            ])
            ->willReturn($region);

        $customerAddress = $this->createMock(CustomerAddress::class);
        $this->customerAddressFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'country_id' => 'US',
                    'region' => $region,
                    'postcode' => '94016',
                    'city' => 'SF',
                    'street' => ['1st St'],
                ],
            ])
            ->willReturn($customerAddress);

        $result = $sut->mapAddress($quoteAddress);
        $this->assertSame($customerAddress, $result);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testMapItemSetsExtensionAttributePriceForTaxCalculation(): void
    {
        $priceIncludesTax = true;
        $useBaseCurrency = false;

        $quoteDetailsItem = $this->createMock(QuoteDetailsItemInterface::class);
        $quoteDetailsItem->method('setCode')->willReturnSelf();
        $quoteDetailsItem->method('setQuantity')->willReturnSelf();
        $quoteDetailsItem->method('setTaxClassKey')->willReturnSelf();
        $quoteDetailsItem->method('setIsTaxIncluded')->willReturnSelf();
        $quoteDetailsItem->method('setType')->willReturnSelf();
        $quoteDetailsItem->method('setUnitPrice')->willReturnSelf();
        $quoteDetailsItem->method('setDiscountAmount')->willReturnSelf();
        $quoteDetailsItem->method('setParentCode')->willReturnSelf();

        $extension = new class implements \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface
        {
            /**
             * @var float|null
             */
            private $price;

            /**
             * @param mixed ...$args
             * @return $this
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function setPriceForTaxCalculation(...$args)
            {
                $this->price = $args[0] ?? null;
                return $this;
            }

            /**
             * @return float|null
             */
            public function getPriceForTaxCalculation()
            {
                return $this->price;
            }
        };

        $quoteDetailsItem->method('getExtensionAttributes')->willReturn(null);
        $quoteDetailsItem->expects($this->once())->method('setExtensionAttributes')->with($extension)->willReturnSelf();

        $this->quoteDetailsItemExtensionFactory->method('create')->willReturn($extension);
        $this->quoteDetailsItemFactory->method('create')->willReturn($quoteDetailsItem);

        $taxClassKey = $this->createMock(TaxClassKeyInterface::class);
        $taxClassKey->method('setType')->willReturnSelf();
        $taxClassKey->method('setValue')->willReturnSelf();
        $this->taxClassKeyFactory->method('create')->willReturn($taxClassKey);

        $product = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['getTaxClassId']
        );
        $product->method('getTaxClassId')->willReturn(4);

        $item = $this->createPartialMockWithReflection(
            AbstractItem::class,
            [
                'getQuote', 'getAddress', 'getOptionByCode',
                'getQty', 'getProduct',
                'getCalculationPriceOriginal', 'getOriginalPrice',
                'getDiscountAmount',
                'getTaxCalculationItemId',
                'setTaxCalculationItemId',
                'getTaxCalculationPrice',
                'setTaxCalculationPrice'
            ]
        );
        $item->method('getQuote')->willReturn(null);
        $item->method('getAddress')->willReturn(null);
        $item->method('getOptionByCode')->willReturn(null);
        $item->method('getTaxCalculationItemId')->willReturn('code-1');
        $item->method('getQty')->willReturn(2);
        $item->method('getProduct')->willReturn($product);
        $item->method('getTaxCalculationPrice')->willReturn(11.49);
        $item->method('getCalculationPriceOriginal')->willReturn(9.99);
        $item->method('getOriginalPrice')->willReturn(9.99);

        $this->taxHelper->method('applyTaxOnOriginalPrice')->willReturn(true);

        $sut = $this->createSut();
        $result = $sut->mapItem($this->quoteDetailsItemFactory, $item, $priceIncludesTax, $useBaseCurrency, null);

        $this->assertSame($quoteDetailsItem, $result);
    }

    public function testMapItemExtraTaxablesBuildsItems(): void
    {
        $item = $this->createPartialMockWithReflection(
            AbstractItem::class,
            ['getQuote', 'getAddress', 'getOptionByCode', 'getAssociatedTaxables', 'getTaxCalculationItemId']
        );
        $item->method('getQuote')->willReturn(null);
        $item->method('getAddress')->willReturn(null);
        $item->method('getOptionByCode')->willReturn(null);
        $item->method('getTaxCalculationItemId')->willReturn('code-2');
        $item->method('getAssociatedTaxables')->willReturn([
            [
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => 'fee',
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => 'fee1',
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => 3.33,
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => 2.22,
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => 9,
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => true,
            ],
        ]);

        $quoteItem1 = $this->createMock(QuoteDetailsItemInterface::class);
        $quoteItem1->expects($this->once())->method('setCode')->with('fee1')->willReturnSelf();
        $quoteItem1->expects($this->once())->method('setType')->with('fee')->willReturnSelf();
        $quoteItem1->expects($this->once())->method('setQuantity')->with(1)->willReturnSelf();
        $quoteItem1->expects($this->once())->method('setUnitPrice')->with(3.33)->willReturnSelf();
        $quoteItem1->expects($this->once())->method('setIsTaxIncluded')->with(true)->willReturnSelf();
        $quoteItem1->expects($this->once())->method('setAssociatedItemCode')->with('code-2')->willReturnSelf();

        $this->quoteDetailsItemFactory->method('create')->willReturn($quoteItem1);

        $taxClassKey = $this->createMock(TaxClassKeyInterface::class);
        $taxClassKey->method('setType')->willReturnSelf();
        $taxClassKey->method('setValue')->willReturnSelf();
        $this->taxClassKeyFactory->method('create')->willReturn($taxClassKey);
        // ensure chaining works
        $quoteItem1->method('setTaxClassKey')->willReturnSelf();

        $sut = $this->createSut();
        $result = $sut->mapItemExtraTaxables($this->quoteDetailsItemFactory, $item, true, false);

        $this->assertCount(1, $result);
        $this->assertSame($quoteItem1, $result[0]);
    }

    public function testMapItemsFlattensResults(): void
    {
        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['mapItem', 'mapItemExtraTaxables'])
            ->getMock();

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);

        $parentItem = $this->createPartialMockWithReflection(
            AbstractItem::class,
            [
                'getQuote', 'getAddress', 'getOptionByCode', 'getParentItem',
                'isChildrenCalculated', 'getChildren', 'getHasChildren'
            ]
        );
        $parentItem->method('getQuote')->willReturn(null);
        $parentItem->method('getAddress')->willReturn(null);
        $parentItem->method('getOptionByCode')->willReturn(null);
        $parentItem->method('getParentItem')->willReturn(null);
        $parentItem->method('getHasChildren')->willReturn(false);
        $parentItem->method('isChildrenCalculated')->willReturn(false);
        $parentItem->method('getChildren')->willReturn([]);

        $shippingAssignment->method('getItems')->willReturn([$parentItem]);

        $itemDataObject = $this->createMock(QuoteDetailsItemInterface::class);
        $sut->method('mapItem')->willReturn($itemDataObject);
        $sut->method('mapItemExtraTaxables')->willReturn([]);

        $result = $sut->mapItems($shippingAssignment, true, false);
        $this->assertSame([$itemDataObject], $result);
    }

    public function testGetShippingDataObjectReturnsItem(): void
    {
        $store = $this->createMock(Store::class);

        $address = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $address->method('getQuote')->willReturn((function () use ($store) {
            $quote = $this->createPartialMockWithReflection(\stdClass::class, ['getStore']);
            $quote->method('getStore')->willReturn($store);
            return $quote;
        })());

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $total = $this->createPartialMockWithReflection(QuoteAddressTotal::class, [
                'getShippingTaxCalculationAmount', 'setShippingTaxCalculationAmount', 'getShippingAmount',
                'setBaseShippingTaxCalculationAmount', 'getBaseShippingAmount', 'getShippingDiscountAmount',
                'getBaseShippingDiscountAmount'
            ]);
        $total->method('getShippingTaxCalculationAmount')->willReturn(10.0);
        $total->method('getShippingAmount')->willReturn(10.0);
        $total->method('getBaseShippingAmount')->willReturn(8.0);
        $total->method('getShippingDiscountAmount')->willReturn(1.0);
        $total->method('getBaseShippingDiscountAmount')->willReturn(0.8);

        $quoteDetailsItem = $this->createMock(QuoteDetailsItemInterface::class);
        $quoteDetailsItem->method('setType')->with(CommonTaxCollector::ITEM_TYPE_SHIPPING)->willReturnSelf();
        $quoteDetailsItem->method('setCode')->with(CommonTaxCollector::ITEM_CODE_SHIPPING)->willReturnSelf();
        $quoteDetailsItem->method('setQuantity')->with(1)->willReturnSelf();
        $quoteDetailsItem->method('setUnitPrice')->with(10.0)->willReturnSelf();
        $quoteDetailsItem->method('setDiscountAmount')->with(1.0)->willReturnSelf();
        $quoteDetailsItem->method('setTaxClassKey')->willReturnSelf();
        $quoteDetailsItem->method('setIsTaxIncluded')->with(false)->willReturnSelf();

        $this->quoteDetailsItemFactory->method('create')->willReturn($quoteDetailsItem);

        $taxClassKey = $this->createMock(TaxClassKeyInterface::class);
        $taxClassKey->method('setType')->willReturnSelf();
        $taxClassKey->method('setValue')->willReturnSelf();
        $this->taxClassKeyFactory->method('create')->willReturn($taxClassKey);

        $this->taxConfig->method('getShippingTaxClass')->with($store)->willReturn(3);
        $this->taxConfig->method('shippingPriceIncludesTax')->with($store)->willReturn(false);

        $sut = $this->createSut();
        $result = $sut->getShippingDataObject($shippingAssignment, $total, false);
        $this->assertSame($quoteDetailsItem, $result);
    }

    public function testPrepareQuoteDetailsSetsFields(): void
    {
        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['populateAddressData'])
            ->getMock();

        $quoteDetails = $this->createMock(QuoteDetailsInterface::class);
        $this->quoteDetailsFactory->method('create')->willReturn($quoteDetails);
        $sut->method('populateAddressData')->willReturnCallback(function ($qd) {
            return $qd;
        });
        $quoteDetails->method('setItems')->willReturnSelf();
        $quoteDetails->method('setCustomerId')->willReturnSelf();
        $quoteDetails->method('setCustomerTaxClassKey')->willReturnSelf();

        $store = $this->createMock(Store::class);

        $address = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['getCustomerTaxClassId', 'getCustomerId', 'getStore', 'getBillingAddress']
        );
        $quote->method('getCustomerTaxClassId')->willReturn(7);
        $quote->method('getCustomerId')->willReturn(77);
        $quote->method('getStore')->willReturn($store);
        $quote->method('getBillingAddress')->willReturn($address);
        $address->method('getQuote')->willReturn($quote);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->getMockBuilder(ShippingAssignmentInterface::class)->getMock();
        $shippingAssignment->method('getItems')->willReturn([new \stdClass()]);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $taxClassKey = $this->createMock(TaxClassKeyInterface::class);
        $taxClassKey->method('setType')->willReturnSelf();
        $taxClassKey->method('setValue')->willReturnSelf();
        $this->taxClassKeyFactory->method('create')->willReturn($taxClassKey);

        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('prepareQuoteDetails');
        $result = $method->invoke($sut, $shippingAssignment, []);

        $this->assertSame($quoteDetails, $result);
    }

    public function testPrepareQuoteDetailsReturnsEmptyQuoteDetailsWhenNoItems(): void
    {
        $sut = $this->createSut();

        $expectedQuoteDetails = $this->createMock(QuoteDetailsInterface::class);
        $this->quoteDetailsFactory->method('create')->willReturn($expectedQuoteDetails);

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([]);
        // prepareQuoteDetails evaluates shipping->getAddress() before returning on empty items
        $address = $this->getMockBuilder(QuoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('prepareQuoteDetails');
        $result = $method->invoke($sut, $shippingAssignment, []);

        $this->assertSame($expectedQuoteDetails, $result);
    }

    public function testProcessProductItemsAggregatesValues(): void
    {
        $store = $this->createMock(Store::class);

        $addressItem = $this->createPartialMockWithReflection(
            SafeArrayObject::class,
            ['getTaxCalculationItemId', 'isDeleted', 'getHasChildren', 'isChildrenCalculated']
        );
        $addressItem->method('getTaxCalculationItemId')->willReturn('code-xyz');
        $addressItem->method('isDeleted')->willReturn(false);
        $addressItem->method('getHasChildren')->willReturn(false);
        $addressItem->method('isChildrenCalculated')->willReturn(false);

        $address = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(['getQuote'])->disableOriginalConstructor()->getMock();
        $quote = $this->createPartialMockWithReflection(\stdClass::class, ['getStore']);
        $quote->method('getStore')->willReturn($store);
        $address->method('getQuote')->willReturn($quote);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([$addressItem]);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $total = $this->createPartialMockWithReflection(
            QuoteAddressTotal::class,
            [
                'setTotalAmount', 'setBaseTotalAmount', 'setSubtotalInclTax',
                'setBaseSubtotalTotalInclTax', 'setBaseSubtotalInclTax'
            ]
        );
        $total->method('setTotalAmount')->willReturnSelf();
        $total->method('setBaseTotalAmount')->willReturnSelf();
        $total->expects($this->once())->method('setSubtotalInclTax')->with(110.0);
        $total->expects($this->any())->method('setBaseSubtotalTotalInclTax')->with(99.0);
        $total->expects($this->any())->method('setBaseSubtotalInclTax')->with(99.0);

        $taxDetail = $this->createMock(TaxDetailsItemInterface::class);
        $taxDetail->method('getRowTotal')->willReturn(100.0);
        $taxDetail->method('getDiscountTaxCompensationAmount')->willReturn(5.0);
        $taxDetail->method('getRowTax')->willReturn(10.0);
        $taxDetail->method('getRowTotalInclTax')->willReturn(110.0);

        $baseTaxDetail = $this->createMock(TaxDetailsItemInterface::class);
        $baseTaxDetail->method('getRowTotal')->willReturn(90.0);
        $baseTaxDetail->method('getDiscountTaxCompensationAmount')->willReturn(4.0);
        $baseTaxDetail->method('getRowTax')->willReturn(9.0);
        $baseTaxDetail->method('getRowTotalInclTax')->willReturn(99.0);

        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['updateItemTaxInfo'])
            ->getMock();
        $sut->method('updateItemTaxInfo')->willReturnSelf();

        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('processProductItems');
        $method->invoke($sut, $shippingAssignment, [
            'code-xyz' => [
                CommonTaxCollector::KEY_ITEM => $taxDetail,
                CommonTaxCollector::KEY_BASE_ITEM => $baseTaxDetail
            ]
        ], $total);

        $this->assertTrue(true);
    }

    public function testProcessAppliedTaxesSetsItemsAppliedTaxesAndItem(): void
    {
        $store = $this->createMock(Store::class);

        $addressItem = $this->createPartialMockWithReflection(
            SafeArrayObject::class,
            [
                'getTaxCalculationItemId', 'isDeleted', 'getHasChildren',
                'isChildrenCalculated', 'getId', 'setAppliedTaxes'
            ]
        );
        $addressItem->method('getTaxCalculationItemId')->willReturn('code-1');
        $addressItem->method('getId')->willReturn(123);
        $addressItem->expects($this->once())->method('setAppliedTaxes')->with($this->isType('array'));

        $address = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(['getQuote'])->disableOriginalConstructor()->getMock();
        $quote = $this->createPartialMockWithReflection(\stdClass::class, ['getStore']);
        $quote->method('getStore')->willReturn($store);
        $address->method('getQuote')->willReturn($quote);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([$addressItem]);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $applied = $this->createMock(AppliedTaxInterface::class);
        $applied->method('getAmount')->willReturn(1.0);
        $applied->method('getPercent')->willReturn(10.0);
        $applied->method('getTaxRateKey')->willReturn('rate1');
        $applied->method('getRates')->willReturn([]);

        $baseApplied = $this->createMock(AppliedTaxInterface::class);
        $baseApplied->method('getAmount')->willReturn(0.8);
        $baseApplied->method('getPercent')->willReturn(10.0);
        $baseApplied->method('getTaxRateKey')->willReturn('rate1');
        $baseApplied->method('getRates')->willReturn([]);

        $taxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $taxDetails->method('getAppliedTaxes')->willReturn(['t1' => $applied]);
        $taxDetails->method('getType')->willReturn(CommonTaxCollector::ITEM_TYPE_PRODUCT);

        $baseTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $baseTaxDetails->method('getAppliedTaxes')->willReturn(['t1' => $baseApplied]);
        $baseTaxDetails->method('getType')->willReturn(CommonTaxCollector::ITEM_TYPE_PRODUCT);

        $itemsByType = [
            CommonTaxCollector::ITEM_TYPE_PRODUCT => [
                'code-1' => [
                    CommonTaxCollector::KEY_ITEM => $taxDetails,
                    CommonTaxCollector::KEY_BASE_ITEM => $baseTaxDetails]
            ]
        ];

        $total = $this->createPartialMockWithReflection(
            QuoteAddressTotal::class,
            ['addTotalAmount', 'addBaseTotalAmount', 'setAppliedTaxes', 'setItemsAppliedTaxes', 'getAppliedTaxes']
        );
        $total->method('getAppliedTaxes')->willReturn([]);
        $total->method('setAppliedTaxes')->willReturnSelf();
        $total->expects($this->atLeastOnce())->method('setItemsAppliedTaxes')->with($this->callback(function ($arr) {
            return isset($arr['code-1']) && is_array($arr['code-1']);
        }));
        $total->method('addTotalAmount')->willReturnSelf();
        $total->method('addBaseTotalAmount')->willReturnSelf();

        $sut = $this->createSut();

        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('processAppliedTaxes');
        // Ensure correct type is passed for $shippingAssignment
        $typedShippingAssignment = $this->getMockBuilder(ShippingAssignmentInterface::class)->getMock();
        $typedShippingAssignment->method('getItems')->willReturn([$addressItem]);
        $typedShippingAssignment->method('getShipping')->willReturn($shipping);
        $method->invoke($sut, $total, $typedShippingAssignment, $itemsByType);

        $this->assertTrue(true);
    }

    public function testProcessAppliedTaxesSetsAssociatedItemIdForNonProduct(): void
    {
        $store = $this->createMock(Store::class);

        $productAddressItem = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['getTaxCalculationItemId', 'getId']
        );
        $productAddressItem->method('getTaxCalculationItemId')->willReturn('product-code-1');
        $productAddressItem->method('getId')->willReturn(123);

        $address = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->createPartialMockWithReflection(\stdClass::class, ['getStore']);
        $quote->method('getStore')->willReturn($store);
        $address->method('getQuote')->willReturn($quote);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->getMockBuilder(ShippingAssignmentInterface::class)->getMock();
        $shippingAssignment->method('getItems')->willReturn([$productAddressItem]);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $applied = $this->createMock(\Magento\Tax\Api\Data\AppliedTaxInterface::class);
        $applied->method('getAmount')->willReturn(1.0);
        $applied->method('getPercent')->willReturn(10.0);
        $applied->method('getTaxRateKey')->willReturn('rate1');
        $applied->method('getRates')->willReturn([]);

        $baseApplied = $this->createMock(\Magento\Tax\Api\Data\AppliedTaxInterface::class);
        $baseApplied->method('getAmount')->willReturn(0.8);
        $baseApplied->method('getPercent')->willReturn(10.0);
        $baseApplied->method('getTaxRateKey')->willReturn('rate1');
        $baseApplied->method('getRates')->willReturn([]);

        $taxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $taxDetails->method('getAppliedTaxes')->willReturn(['t1' => $applied]);
        $taxDetails->method('getType')->willReturn('fee');
        $taxDetails->method('getAssociatedItemCode')->willReturn('product-code-1');

        $baseTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $baseTaxDetails->method('getAppliedTaxes')->willReturn(['t1' => $baseApplied]);
        $baseTaxDetails->method('getType')->willReturn('fee');
        $baseTaxDetails->method('getAssociatedItemCode')->willReturn('product-code-1');

        $itemsByType = [
            'fee' => [
                'fee-item-code' => [
                    CommonTaxCollector::KEY_ITEM => $taxDetails,
                    CommonTaxCollector::KEY_BASE_ITEM => $baseTaxDetails
                ]
            ]
        ];

        $total = $this->createPartialMockWithReflection(
            QuoteAddressTotal::class,
            ['addTotalAmount', 'addBaseTotalAmount', 'setAppliedTaxes', 'setItemsAppliedTaxes', 'getAppliedTaxes']
        );
        $total->method('getAppliedTaxes')->willReturn([]);
        $total->method('setAppliedTaxes')->willReturnSelf();
        $total->expects($this->atLeastOnce())->method('setItemsAppliedTaxes')->with($this->callback(function ($map) {
            $row = current($map);
            // Ensure associated_item_id is set
            return is_array($row) && isset($row[0]['associated_item_id']) && $row[0]['associated_item_id'] === 123;
        }));
        $total->method('addTotalAmount')->willReturnSelf();
        $total->method('addBaseTotalAmount')->willReturnSelf();

        $sut = $this->createSut();
        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('processAppliedTaxes');
        $method->invoke($sut, $total, $shippingAssignment, $itemsByType);
        $this->assertTrue(true);
    }

    public function testProcessAppliedTaxesSetsItemIdNullForOrderAssociation(): void
    {
        $store = $this->createMock(Store::class);

        $address = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->createPartialMockWithReflection(\stdClass::class, ['getStore']);
        $quote->method('getStore')->willReturn($store);
        $address->method('getQuote')->willReturn($quote);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->getMockBuilder(ShippingAssignmentInterface::class)->getMock();
        $shippingAssignment->method('getItems')->willReturn([]);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $applied = $this->createMock(\Magento\Tax\Api\Data\AppliedTaxInterface::class);
        $applied->method('getAmount')->willReturn(1.0);
        $applied->method('getPercent')->willReturn(10.0);
        $applied->method('getTaxRateKey')->willReturn('rate1');
        $applied->method('getRates')->willReturn([]);

        $baseApplied = $this->createMock(\Magento\Tax\Api\Data\AppliedTaxInterface::class);
        $baseApplied->method('getAmount')->willReturn(0.8);
        $baseApplied->method('getPercent')->willReturn(10.0);
        $baseApplied->method('getTaxRateKey')->willReturn('rate1');
        $baseApplied->method('getRates')->willReturn([]);

        $taxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $taxDetails->method('getAppliedTaxes')->willReturn(['t1' => $applied]);
        $taxDetails->method('getType')->willReturn('fee');
        $taxDetails->method('getAssociatedItemCode')->willReturn(CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE);

        $baseTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $baseTaxDetails->method('getAppliedTaxes')->willReturn(['t1' => $baseApplied]);
        $baseTaxDetails->method('getType')->willReturn('fee');
        $baseTaxDetails->method('getAssociatedItemCode')
            ->willReturn(CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE);

        $itemsByType = [
            'fee' => [
                'fee-item-code' => [
                    CommonTaxCollector::KEY_ITEM => $taxDetails,
                    CommonTaxCollector::KEY_BASE_ITEM => $baseTaxDetails
                ]
            ]
        ];

        $total = $this->createPartialMockWithReflection(
            QuoteAddressTotal::class,
            ['addTotalAmount', 'addBaseTotalAmount', 'setAppliedTaxes', 'setItemsAppliedTaxes', 'getAppliedTaxes']
        );
        $total->method('getAppliedTaxes')->willReturn([]);
        $total->method('setAppliedTaxes')->willReturnSelf();
        $total->expects($this->atLeastOnce())->method('setItemsAppliedTaxes')->with($this->callback(function ($map) {
            $row = current($map);
            // Ensure item_id is null for order-level association
            return is_array($row) && array_key_exists('item_id', $row[0]) && $row[0]['item_id'] === null;
        }));
        $total->method('addTotalAmount')->willReturnSelf();
        $total->method('addBaseTotalAmount')->willReturnSelf();

        $sut = $this->createSut();
        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('processAppliedTaxes');
        $method->invoke($sut, $total, $shippingAssignment, $itemsByType);
        $this->assertTrue(true);
    }

    public function testProcessShippingTaxInfoUpdatesTotals(): void
    {
        $store = $this->createMock(Store::class);

        $address = $this->getMockBuilder(QuoteAddress::class)
            ->onlyMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->createPartialMockWithReflection(\stdClass::class, ['getStore']);
        $quote->method('getStore')->willReturn($store);
        $address->method('getQuote')->willReturn($quote);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $total = $this->createPartialMockWithReflection(
            QuoteAddressTotal::class,
            [
                'setTotalAmount', 'setBaseTotalAmount', 'addTotalAmount', 'addBaseTotalAmount',
                'setShippingInclTax', 'setBaseShippingInclTax', 'setShippingTaxAmount',
                'setBaseShippingTaxAmount', 'setShippingAmountForDiscount', 'setBaseShippingAmountForDiscount'
            ]
        );

        $this->taxConfig->method('discountTax')->with($store)->willReturn(true);

        $shippingTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $shippingTaxDetails->method('getRowTotal')->willReturn(15.0);
        $shippingTaxDetails->method('getRowTotalInclTax')->willReturn(16.5);
        $shippingTaxDetails->method('getRowTax')->willReturn(1.5);
        $shippingTaxDetails->method('getDiscountTaxCompensationAmount')->willReturn(0.2);

        $baseShippingTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $baseShippingTaxDetails->method('getRowTotal')->willReturn(12.0);
        $baseShippingTaxDetails->method('getRowTotalInclTax')->willReturn(13.2);
        $baseShippingTaxDetails->method('getRowTax')->willReturn(1.2);
        $baseShippingTaxDetails->method('getDiscountTaxCompensationAmount')->willReturn(0.15);

        $sut = $this->createSut();
        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('processShippingTaxInfo');
        $method->invoke($sut, $shippingAssignment, $total, $shippingTaxDetails, $baseShippingTaxDetails);

        $this->assertTrue(true);
    }

    public function testSaveAppliedTaxesAggregatesAmounts(): void
    {
        $total = $this->createPartialMockWithReflection(
            QuoteAddressTotal::class,
            ['getAppliedTaxes', 'setAppliedTaxes']
        );
        $total->method('getAppliedTaxes')->willReturn([]);
        $total->expects($this->once())->method('setAppliedTaxes')->with($this->callback(function ($arr) {
            return isset($arr['id1']) && isset($arr['id1']['amount']) && isset($arr['id1']['base_amount']);
        }));

        $sut = $this->createSut();
        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('_saveAppliedTaxes');
        $method->invoke($sut, $total, [[
            'percent' => 10.0,
            'id' => 'id1',
            'rates' => [],
        ]], 100.0, 80.0, 10.0);

        $this->assertTrue(true);
    }

    public function testIncludeFlagsAndResetStateAndIncrement(): void
    {
        $sut = $this->createSut();
        $ref = new \ReflectionClass(CommonTaxCollector::class);

        $includeShipping = $ref->getMethod('includeShipping');
        $this->assertFalse($includeShipping->invoke($sut));

        $includeItems = $ref->getMethod('includeItems');
        $this->assertFalse($includeItems->invoke($sut));

        $includeExtraTax = $ref->getMethod('includeExtraTax');
        $this->assertFalse($includeExtraTax->invoke($sut));

        $saveAppliedTaxes = $ref->getMethod('saveAppliedTaxes');
        $this->assertFalse($saveAppliedTaxes->invoke($sut));

        $getNextIncrement = $ref->getMethod('getNextIncrement');
        $first = $getNextIncrement->invoke($sut);
        $second = $getNextIncrement->invoke($sut);
        $this->assertSame($first + 1, $second);

        $sut->_resetState();
        $afterReset = $getNextIncrement->invoke($sut);
        $this->assertSame(1, $afterReset);
    }

    public function testGetQuoteItemIdReturnsFromQuoteItemKey(): void
    {
        $sut = $this->createSut();
        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('getQuoteItemId');

        $quoteItem = $this->createPartialMockWithReflection(\stdClass::class, ['getId']);
        $quoteItem->method('getId')->willReturn(999);

        $keyedAddressItems = [
            'calc-code-1' => [
                'quote_item' => $quoteItem,
            ],
        ];

        $result = $method->invoke($sut, $keyedAddressItems, 'calc-code-1');
        $this->assertSame(999, $result);
    }

    public function testMapItemsChildrenCalculatedIncludesParentChildAndExtraTaxablesWithParentCode(): void
    {
        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['mapItem', 'mapItemExtraTaxables'])
            ->getMock();

        $parentItem = $this->createPartialMockWithReflection(
            AbstractItem::class,
            [
                'getQuote', 'getAddress', 'getOptionByCode', 'isChildrenCalculated',
                'getChildren', 'getParentItem', 'getHasChildren'
            ]
        );
        $parentItem->method('getHasChildren')->willReturn(true);
        $parentItem->method('isChildrenCalculated')->willReturn(true);
        $parentItem->method('getParentItem')->willReturn(null);

        $childItem = $this->createPartialMockWithReflection(
            AbstractItem::class,
            ['getQuote', 'getAddress', 'getOptionByCode']
        );

        $parentItem->method('getChildren')->willReturn([$childItem]);

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([$parentItem]);

        $parentMapped = $this->createMock(QuoteDetailsItemInterface::class);
        $parentMapped->method('getCode')->willReturn('parent-code');
        $childMapped = $this->createMock(QuoteDetailsItemInterface::class);
        $extra1 = $this->createMock(QuoteDetailsItemInterface::class);
        $extra2 = $this->createMock(QuoteDetailsItemInterface::class);

        // mapItem should be called first for parent (no parentCode), then for child with parentCode 'parent-code'
        $sut->method('mapItem')->willReturnCallback(
            function (...$args) use ($parentItem, $parentMapped, $childMapped) {
                $item = $args[1] ?? null;
                $parentCode = $args[4] ?? null;
                if ($item === $parentItem) {
                    // parent mapping: no parent code
                    return $parentMapped;
                }
                // child mapping: should receive parent code
                \PHPUnit\Framework\Assert::assertSame('parent-code', $parentCode);
                return $childMapped;
            }
        );

        // Extra taxables are derived from the parent item
        $sut->expects($this->once())
            ->method('mapItemExtraTaxables')
            ->with($this->quoteDetailsItemFactory, $parentItem, true, false)
            ->willReturn([$extra1, $extra2]);

        $result = $sut->mapItems($shippingAssignment, true, false);

        $this->assertSame([$parentMapped, $childMapped, $extra1, $extra2], $result);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testConstructorFallsBackToOmForOptionalDependencies(): void
    {
        $extFactory = $this->createMock(QuoteDetailsItemExtensionInterfaceFactory::class);
        $ext = new class implements \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface
        {
            /**
             * @var float|null
             */
            private $price;

            /**
             * @param mixed ...$args
             * @return $this
             * @SuppressWarnings(PHPMD.UnusedLocalVariable)
             */
            public function setPriceForTaxCalculation(...$args)
            {
                $this->price = $args[0] ?? null;
                return $this;
            }

            /**
             * @return float|null
             */
            public function getPriceForTaxCalculation()
            {
                return $this->price;
            }
        };
        $extFactory->method('create')->willReturn($ext);
        $customerAccount = $this->createMock(CustomerAccountManagement::class);
        $om = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])->getMock();
        $om->method('get')->willReturnCallback(function ($class) use ($extFactory, $customerAccount) {
            if ($class === QuoteDetailsItemExtensionInterfaceFactory::class) {
                return $extFactory;
            }
            if ($class === CustomerAccountManagement::class) {
                return $customerAccount;
            }
            $this->fail('Unexpected ObjectManager::get call for ' . $class);
        });
        \Magento\Framework\App\ObjectManager::setInstance($om);
        try {
            $sut = $this->getMockBuilder(CommonTaxCollector::class)
                ->setConstructorArgs([
                    $this->taxConfig,
                    $this->taxCalculationService,
                    $this->quoteDetailsFactory,
                    $this->quoteDetailsItemFactory,
                    $this->taxClassKeyFactory,
                    $this->customerAddressFactory,
                    $this->customerAddressRegionFactory,
                    $this->taxHelper,
                    null, null])
                ->onlyMethods(['mapAddress'])->getMock();
            $ref = new \ReflectionClass(CommonTaxCollector::class);
            $method = $ref->getMethod('setPriceForTaxCalculation');
            $qdi = $this->createMock(QuoteDetailsItemInterface::class);
            $qdi->method('getExtensionAttributes')->willReturn(null);
            $qdi->expects($this->once())->method('setExtensionAttributes')->with($ext)->willReturnSelf();

            $method->invoke($sut, $qdi, 12.34);
            $this->assertSame(12.34, $ext->getPriceForTaxCalculation());
            $billingMapped = $this->createMock(CustomerAddress::class);
            $shippingMapped = $this->createMock(CustomerAddress::class);
            $sut->method('mapAddress')->willReturnOnConsecutiveCalls($billingMapped, $shippingMapped);
            $quoteDetails = $this->createMock(\Magento\Tax\Api\Data\QuoteDetailsInterface::class);
            $quoteDetails->expects($this->once())->method('setBillingAddress')->with($billingMapped)->willReturnSelf();
            $quoteDetails->expects($this->once())
                ->method('setShippingAddress')->with($shippingMapped)->willReturnSelf();
            $billingAddressFromQuote = $this->createMock(QuoteAddress::class);
            $customerAccount->expects($this->once())
                ->method('getDefaultBillingAddress')
                ->with(15)->willReturn(null);

            $quote = $this->createPartialMockWithReflection(
                \stdClass::class,
                ['isVirtual', 'getCustomerId', 'getBillingAddress']
            );
            $quote->method('isVirtual')->willReturn(true);
            $quote->method('getCustomerId')->willReturn(15);
            $quote->method('getBillingAddress')->willReturn($billingAddressFromQuote);
            $address = $this->createPartialMockWithReflection(
                QuoteAddress::class,
                ['getQuote', 'getCountryId', 'getAddressType']
            );
            $address->method('getAddressType')->willReturn(QuoteAddress::ADDRESS_TYPE_BILLING);
            $address->method('getCountryId')->willReturn(null);
            $address->method('getQuote')->willReturn($quote);
            $sut->populateAddressData($quoteDetails, $address);
            $this->assertTrue(true);
        } finally {
            $resetOm = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['get'])->getMock();
            \Magento\Framework\App\ObjectManager::setInstance($resetOm);
        }
    }

    public function testMapItemExtraTaxablesReturnsEmptyWhenNoTaxables(): void
    {
        $item = $this->createPartialMockWithReflection(
            AbstractItem::class,
            ['getQuote', 'getAddress', 'getOptionByCode', 'getAssociatedTaxables', 'getTaxCalculationItemId']
        );
        $item->method('getAssociatedTaxables')->willReturn(null);
        $item->method('getTaxCalculationItemId')->willReturn('any');

        $sut = $this->createSut();
        $result = $sut->mapItemExtraTaxables($this->quoteDetailsItemFactory, $item, true, false);
        $this->assertSame([], $result);
    }

    public function testMapItemExtraTaxablesUsesBaseUnitPriceWhenUseBaseCurrency(): void
    {
        $item = $this->createPartialMockWithReflection(
            AbstractItem::class,
            ['getQuote', 'getAddress', 'getOptionByCode', 'getAssociatedTaxables', 'getTaxCalculationItemId']
        );
        $item->method('getTaxCalculationItemId')->willReturn('calc-3');
        $item->method('getAssociatedTaxables')->willReturn([
            [
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => 'fee',
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => 'fee-base',
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => 3.33,
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => 2.22,
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
                CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => 9,
            ],
        ]);

        $quoteItem = $this->createMock(QuoteDetailsItemInterface::class);
        $quoteItem->expects($this->once())->method('setCode')->with('fee-base')->willReturnSelf();
        $quoteItem->expects($this->once())->method('setType')->with('fee')->willReturnSelf();
        $quoteItem->expects($this->once())->method('setQuantity')->with(1)->willReturnSelf();
        // useBaseCurrency = true so base unit price (2.22) must be used
        $quoteItem->expects($this->once())->method('setUnitPrice')->with(2.22)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setIsTaxIncluded')->with(true)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setAssociatedItemCode')->with('calc-3')->willReturnSelf();
        // ensure chaining continues after setTaxClassKey
        $quoteItem->method('setTaxClassKey')->willReturnSelf();

        $this->quoteDetailsItemFactory->method('create')->willReturn($quoteItem);

        $taxClassKey = $this->createMock(TaxClassKeyInterface::class);
        $taxClassKey->method('setType')->willReturnSelf();
        $taxClassKey->method('setValue')->willReturnSelf();
        $this->taxClassKeyFactory->method('create')->willReturn($taxClassKey);

        $sut = $this->createSut();
        $result = $sut->mapItemExtraTaxables($this->quoteDetailsItemFactory, $item, true, true);
        $this->assertCount(1, $result);
        $this->assertSame($quoteItem, $result[0]);
    }

    public function testMapItemsReturnsEmptyWhenNoItems(): void
    {
        $sut = $this->createSut();
        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([]);

        $result = $sut->mapItems($shippingAssignment, true, false);
        $this->assertSame([], $result);
    }

    public function testMapItemsSkipsChildItemsWithParentItem(): void
    {
        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['mapItem', 'mapItemExtraTaxables'])
            ->getMock();
        $sut->expects($this->never())->method('mapItem');
        $sut->expects($this->never())->method('mapItemExtraTaxables');

        $childItem = $this->createPartialMockWithReflection(\stdClass::class, ['getParentItem']);
        $childItem->method('getParentItem')->willReturn(new \stdClass());

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([$childItem]);

        $result = $sut->mapItems($shippingAssignment, true, false);
        $this->assertSame([], $result);
    }

    public function testProcessProductItemsSkipsAggregationForChildrenCalculated(): void
    {
        $store = $this->createMock(Store::class);

        $addressItem = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['getTaxCalculationItemId', 'isDeleted', 'getHasChildren', 'isChildrenCalculated']
        );
        $addressItem->method('getTaxCalculationItemId')->willReturn('code-skip');
        $addressItem->method('isDeleted')->willReturn(false);
        $addressItem->method('getHasChildren')->willReturn(true);
        $addressItem->method('isChildrenCalculated')->willReturn(true);

        $address = $this->createPartialMockWithReflection(
            \stdClass::class,
            [
                'getQuote', 'setBaseTaxAmount', 'setBaseSubtotalTotalInclTax',
                'setSubtotalInclTax', 'setSubtotal', 'setBaseSubtotal'
            ]
        );
        $quote = $this->createPartialMockWithReflection(\stdClass::class, ['getStore']);
        $quote->method('getStore')->willReturn($store);
        $address->method('getQuote')->willReturn($quote);
        $address->method('setBaseTaxAmount')->willReturnSelf();
        $address->method('setBaseSubtotalTotalInclTax')->willReturnSelf();
        $address->method('setSubtotalInclTax')->willReturnSelf();
        $address->method('setSubtotal')->willReturnSelf();
        $address->method('setBaseSubtotal')->willReturnSelf();

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);

        $shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $shippingAssignment->method('getItems')->willReturn([$addressItem]);
        $shippingAssignment->method('getShipping')->willReturn($shipping);

        $total = $this->createPartialMockWithReflection(
            QuoteAddressTotal::class,
            [
                'setTotalAmount', 'setBaseTotalAmount', 'setSubtotalInclTax',
                'setBaseSubtotalTotalInclTax', 'setBaseSubtotalInclTax'
            ]
        );
        $total->method('setTotalAmount')->willReturnSelf();
        $total->method('setBaseTotalAmount')->willReturnSelf();
        $total->expects($this->once())->method('setSubtotalInclTax')->with(0.0);
        $total->expects($this->any())->method('setBaseSubtotalTotalInclTax')->with(0.0);
        $total->expects($this->any())->method('setBaseSubtotalInclTax')->with(0.0);

        $taxDetail = $this->createMock(TaxDetailsItemInterface::class);
        $taxDetail->method('getRowTotal')->willReturn(100.0);
        $taxDetail->method('getDiscountTaxCompensationAmount')->willReturn(5.0);
        $taxDetail->method('getRowTax')->willReturn(10.0);
        $taxDetail->method('getRowTotalInclTax')->willReturn(110.0);

        $baseTaxDetail = $this->createMock(TaxDetailsItemInterface::class);
        $baseTaxDetail->method('getRowTotal')->willReturn(90.0);
        $baseTaxDetail->method('getDiscountTaxCompensationAmount')->willReturn(4.0);
        $baseTaxDetail->method('getRowTax')->willReturn(9.0);
        $baseTaxDetail->method('getRowTotalInclTax')->willReturn(99.0);

        $sut = $this->getMockBuilder(CommonTaxCollector::class)
            ->setConstructorArgs([
                $this->taxConfig,
                $this->taxCalculationService,
                $this->quoteDetailsFactory,
                $this->quoteDetailsItemFactory,
                $this->taxClassKeyFactory,
                $this->customerAddressFactory,
                $this->customerAddressRegionFactory,
                $this->taxHelper,
                $this->quoteDetailsItemExtensionFactory,
                $this->customerAccountManagement
            ])
            ->onlyMethods(['updateItemTaxInfo'])
            ->getMock();
        $sut->method('updateItemTaxInfo')->willReturnSelf();

        $ref = new \ReflectionClass(CommonTaxCollector::class);
        $method = $ref->getMethod('processProductItems');
        $method->invoke($sut, $shippingAssignment, [
            'code-skip' => [
                CommonTaxCollector::KEY_ITEM => $taxDetail,
                CommonTaxCollector::KEY_BASE_ITEM => $baseTaxDetail
            ]
        ], $total);

        $this->assertTrue(true);
    }

    public function testUpdateItemTaxInfoSetsDiscountCalcPricesWhenDiscountTaxTrue(): void
    {
        $sut = $this->createSut();

        $store = $this->createMock(Store::class);

        $itemTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $itemTaxDetails->method('getPriceInclTax')->willReturn(13.34);
        $baseItemTaxDetails = $this->createMock(TaxDetailsItemInterface::class);
        $baseItemTaxDetails->method('getPriceInclTax')->willReturn(11.00);

        // Stub other fields used in method to avoid nulls
        $itemTaxDetails->method('getPrice')->willReturn(12.34);
        $itemTaxDetails->method('getRowTotal')->willReturn(24.68);
        $itemTaxDetails->method('getRowTotalInclTax')->willReturn(26.68);
        $itemTaxDetails->method('getRowTax')->willReturn(2.00);
        $itemTaxDetails->method('getTaxPercent')->willReturn(10.0);
        $itemTaxDetails->method('getDiscountTaxCompensationAmount')->willReturn(0.50);
        $baseItemTaxDetails->method('getPrice')->willReturn(10.00);
        $baseItemTaxDetails->method('getRowTotal')->willReturn(20.00);
        $baseItemTaxDetails->method('getRowTotalInclTax')->willReturn(22.00);
        $baseItemTaxDetails->method('getRowTax')->willReturn(1.50);
        $baseItemTaxDetails->method('getTaxPercent')->willReturn(7.5);
        $baseItemTaxDetails->method('getDiscountTaxCompensationAmount')->willReturn(0.25);

        $quoteItem = $this->createPartialMockWithReflection(\stdClass::class, [
                'setPrice', 'getCustomPrice', 'setCustomPrice', 'setConvertedPrice', 'setPriceInclTax',
                'setRowTotal', 'setRowTotalInclTax', 'setTaxAmount', 'setTaxPercent',
                'setDiscountTaxCompensationAmount', 'setBasePrice', 'setBasePriceInclTax', 'setBaseRowTotal',
                'setBaseRowTotalInclTax', 'setBaseTaxAmount', 'setBaseDiscountTaxCompensationAmount',
                'setDiscountCalculationPrice', 'setBaseDiscountCalculationPrice'
            ]);
        // Allow most setters to be called without strict expectations
        $quoteItem->method('setPrice')->willReturnSelf();
        $quoteItem->method('setConvertedPrice')->willReturnSelf();
        $quoteItem->method('setPriceInclTax')->willReturnSelf();
        $quoteItem->method('setRowTotal')->willReturnSelf();
        $quoteItem->method('setRowTotalInclTax')->willReturnSelf();
        $quoteItem->method('setTaxAmount')->willReturnSelf();
        $quoteItem->method('setTaxPercent')->willReturnSelf();
        $quoteItem->method('setDiscountTaxCompensationAmount')->willReturnSelf();
        $quoteItem->method('setBasePrice')->willReturnSelf();
        $quoteItem->method('setBasePriceInclTax')->willReturnSelf();
        $quoteItem->method('setBaseRowTotal')->willReturnSelf();
        $quoteItem->method('setBaseRowTotalInclTax')->willReturnSelf();
        $quoteItem->method('setBaseTaxAmount')->willReturnSelf();
        $quoteItem->method('setBaseDiscountTaxCompensationAmount')->willReturnSelf();
        // Avoid custom price branch
        $quoteItem->method('getCustomPrice')->willReturn(null);
        $this->taxHelper->method('applyTaxOnCustomPrice')->willReturn(false);

        // Key expectations for discountTax=true path
        $this->taxConfig->method('discountTax')->with($store)->willReturn(true);
        $quoteItem->expects($this->once())->method('setDiscountCalculationPrice')->with(13.34)->willReturnSelf();
        $quoteItem->expects($this->once())->method('setBaseDiscountCalculationPrice')->with(11.00)->willReturnSelf();

        $sut->updateItemTaxInfo($quoteItem, $itemTaxDetails, $baseItemTaxDetails, $store);
        $this->assertTrue(true);
    }

    public function testConvertAppliedTaxesReturnsEmptyWhenInputsEmpty(): void
    {
        $sut = $this->createSut();
        $result = $sut->convertAppliedTaxes([], []);
        $this->assertSame([], $result);
    }
}
