<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Calculation\TotalBaseCalculator;
use Magento\Tax\Model\Config;

/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Tax
 */

use Magento\Tax\Model\Sales\Total\Quote\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxTest extends TestCase
{
    public const TAX = 0.2;

    /**
     * Tests the specific method
     *
     * @param array $itemData
     * @param array $appliedRatesData
     * @param array $taxDetailsData
     * @param array $quoteDetailsData
     * @param array $addressData
     * @param array $verifyData
     *
     * @dataProvider dataProviderCollectArray
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testCollect(
        $itemData,
        $appliedRatesData,
        $taxDetailsData,
        $quoteDetailsData,
        $addressData,
        $verifyData
    ) {
        $this->markTestSkipped('Source code is not testable. Need to be refactored before unit testing');
        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $totalsMock = $this->createMock(Total::class);
        $objectManager = new ObjectManager($this);
        $taxData = $this->createMock(Data::class);
        $taxConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['priceIncludesTax', 'getShippingTaxClass', 'shippingPriceIncludesTax', 'discountTax'])
            ->getMock();
        $taxConfig->method('priceIncludesTax')
            ->willReturn(false);
        $taxConfig->expects($this->any())->method('getShippingTaxClass')
            ->willReturn(1);
        $taxConfig->expects($this->any())->method('shippingPriceIncludesTax')
            ->willReturn(false);
        $taxConfig->expects($this->any())->method('discountTax')
            ->willReturn(false);

        $product = $this->createMock(Product::class);
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getHasChildren', 'getProduct', 'getQuote', 'getCode'])
            ->getMock();
        $item->method('getParentItem')
            ->willReturn(null);
        $item->method('getHasChildren')
            ->willReturn(false);
        $item->method('getCode')
            ->willReturn("1");
        $item->method('getProduct')
            ->willReturn($product);

        foreach ($itemData as $key => $value) {
            $item->setData($key, $value);
        }

        $items = [$item];
        $taxDetails = $this->getMockForAbstractClass(TaxDetailsInterface::class);
        $taxDetails->expects($this->any())->method('getItems')
            ->willReturn($items);

        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getStore',
                    'hasSingleStore',
                    'isSingleStoreMode',
                    'getStores',
                    'getWebsite',
                    'getWebsites',
                    'reinitStores',
                    'getDefaultStoreView',
                    'setIsSingleStoreModeAllowed',
                    'getGroup',
                    'getGroups',
                    'clearWebsiteCache',
                    'setCurrentStore',
                ]
            )
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager->expects($this->any())->method('getStore')
            ->willReturn($storeMock);

        $calculatorFactory = $this->getMockBuilder(CalculatorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $calculationTool = $this->getMockBuilder(Calculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRate', 'getAppliedRates', 'round', 'calcTaxAmount'])
            ->getMock();
        $calculationTool->expects($this->any())->method('round')
            ->willReturnArgument(0);
        $calculationTool->expects($this->any())->method('getRate')
            ->willReturn(20);
        $calculationTool->expects($this->any())->method('calcTaxAmount')
            ->willReturn(20);

        $calculationTool->expects($this->any())->method('getAppliedRates')
            ->willReturn($appliedRatesData);
        $calculator = $objectManager->getObject(
            TotalBaseCalculator::class,
            [
                'calculationTool' => $calculationTool,
            ]
        );
        $calculatorFactory->method('create')
            ->willReturn($calculator);

        $taxCalculationService = $this->getMockForAbstractClass(TaxCalculationInterface::class);

        $taxClassKeyDataObjectMock = $this->getMockForAbstractClass(TaxClassKeyInterface::class);
        $taxClassKeyDataObjectFactoryMock = $this->getMockBuilder(
            TaxClassKeyInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $taxClassKeyDataObjectFactoryMock->method('create')
            ->willReturn($taxClassKeyDataObjectMock);
        $taxClassKeyDataObjectMock->method('setType')
            ->willReturnSelf();
        $taxClassKeyDataObjectMock->method('setValue')
            ->willReturnSelf();

        $itemDataObjectMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
        $itemDataObjectFactoryMock = $this->getMockBuilder(
            QuoteDetailsItemInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $itemDataObjectFactoryMock->method('create')
            ->willReturn($itemDataObjectMock);
        $itemDataObjectMock->method('setTaxClassKey')
            ->willReturnSelf();
        $itemDataObjectMock->method('getAssociatedTaxables')
            ->willReturnSelf();

        $regionFactory = $this->getMockBuilder(RegionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRegionId', 'create'])
            ->getMock();

        $addressFactory = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionBuilder', 'create'])
            ->getMock();
        $region = $this->getMockForAbstractClass(RegionInterface::class, [], '', false);
        $regionFactory->method('setRegionId')
            ->willReturn($regionFactory);
        $regionFactory->method('create')
            ->willReturn($region);
        $addressFactory->method('getRegionBuilder')
            ->willReturn($regionFactory);

        $quoteDetails = $this->getMockForAbstractClass(QuoteDetailsInterface::class);
        $quoteDetailsDataObjectFactoryMock = $this->createPartialMock(
            QuoteDetailsInterfaceFactory::class,
            ['create']
        );
        $quoteDetailsDataObjectFactoryMock->method('create')
            ->willReturn($quoteDetails);

        $quoteDetailsItemDataObjectFactoryMock = $this->createPartialMock(
            QuoteDetailsItemInterfaceFactory::class,
            ['create']
        );

        $taxTotalsCalcModel = new Tax(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactoryMock,
            $quoteDetailsItemDataObjectFactoryMock,
            $taxClassKeyDataObjectFactoryMock,
            $addressFactory,
            $regionFactory,
            $taxData
        );

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['convertPrice', 'getStoreId'])
            ->getMock();
        $store->method('getStoreId')
            ->willReturn(1);
        $quote = $this->createMock(Quote::class);
        $quote->method('getStore')
            ->willReturn($store);
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote', 'getRegionId'])
            ->addMethods(['getAssociatedTaxables', 'getCustomAttributesCodes', 'getBillingAddress'])
            ->getMock();
        $item->method('getQuote')
            ->willReturn($quote);
        $address->method('getQuote')
            ->willReturn($quote);
        $address->method('getAssociatedTaxables')
            ->willReturn([]);
        $address->method('getRegionId')
            ->willReturn($region);
        $address->method('getCustomAttributesCodes')
            ->willReturn([]);
        $quote->method('getBillingAddress')
            ->willReturn($address);
        $addressFactory->method('create')
            ->willReturn($address);

        $addressData["cached_items_all"] = $items;
        foreach ($addressData as $key => $value) {
            $address->setData($key, $value);
        }

        $taxTotalsCalcModel->collect($quote, $shippingAssignmentMock, $totalsMock);
        foreach ($verifyData as $key => $value) {
            $this->assertSame($verifyData[$key], $address->getData($key));
        }
    }

    /**
     * @return array
     */
    public function dataProviderCollectArray()
    {
        $data = [
            'default' => [
                'itemData' => [
                    "qty" => 1, "price" => 100, "tax_percent" => 20, "product_type" => "simple",
                    "code" => "sequence-1", "tax_calculation_item_id" => "sequence-1", "converted_price" => 100,
                ],
                '$appliedRates' => [
                    [
                        "rates" => [
                            [
                                "code" => "US-NY-*-Rate ",
                                "title" => "US-NY-*-Rate ",
                                "percent" => 20,
                                "rate_id" => 1,
                            ],
                        ],
                        "percent" => 20,
                        "id" => "US-NY-*-Rate 1",
                    ],
                ],
                'taxDetailsData' => [
                    "subtotal" => 100,
                    "tax_amount" => 20,
                    "discount_tax_compensation_amount" => 0,
                    "applied_taxes" => [
                        "_data" => [
                            "amount" => 20,
                            "percent" => 20,
                            "rates" => ["_data" => ["percent" => 20]],
                            "tax_rate_key" => "US-NY-*-Rate 1",
                        ],
                    ],
                    'items' => [
                        "sequence-1" => [
                            "_data" => [
                                'code' => 'sequence-1',
                                'type' => 'product',
                                'row_tax' => 20,
                                'price' => 100,
                                'price_incl_tax' => 120,
                                'row_total' => 100,
                                'row_total_incl_tax' => 120,
                                'tax_calculation_item_id' => "sequence-1",
                            ],
                        ],
                    ],
                ],
                'quoteDetailsData' => [
                    "billing_address" => [
                        "street" => ["123 Main Street"],
                        "postcode" => "10012",
                        "country_id" => "US",
                        "region" => ["region_id" => 43],
                        "city" => "New York",
                    ],
                    'shipping_address' => [
                        "street" => ["123 Main Street"],
                        "postcode" => "10012",
                        "country_id" => "US",
                        "region" => ["region_id" => 43],
                        "city" => "New York",
                    ],
                    'customer_id' => '1',
                    'items' => [
                        [
                            'code' => 'sequence-1',
                            'type' => 'product',
                            'quantity' => 1,
                            'unit_price' => 100,
                            'tax_class_key' => ["_data" => ["type" => "id", "value" => 2]],
                            'is_tax_included = false',
                        ],
                    ],
                ],
                'addressData' => [
                    "address_id" => 2, "address_type" => "shipping", "street" => "123 Main Street",
                    "city" => "New York", "region" => "New York", "region_id" => "43", "postcode" => "10012",
                    "country_id" => "US", "telephone" => "111-111-1111", "same_as_billing" => "1",
                    "shipping_method" => "freeshipping_freeshipping", "weight" => 1, "shipping_amount" => 0,
                    "base_shipping_amount" => 0,
                ],
                'verifyData' => [
                    "tax_amount" => 20.0,
                    "subtotal" => 100,
                    "shipping_amount" => 0,
                    "subtotal_incl_tax" => 120.0,
                ],
            ],
        ];

        return $data;
    }

    /**
     * Tests the specific method
     *
     * @param string $calculationSequence
     * @param string $keyExpected
     * @param string $keyAbsent
     * @dataProvider dataProviderProcessConfigArray
     */
    public function testProcessConfigArray($calculationSequence, $keyExpected, $keyAbsent)
    {
        $taxData = $this->createMock(Data::class);
        $taxData->method('getCalculationSequence')
            ->willReturn($calculationSequence);

        $objectManager = new ObjectManager($this);
        $taxTotalsCalcModel = $objectManager->getObject(
            Tax::class,
            ['taxData' => $taxData]
        );
        $array = $taxTotalsCalcModel->processConfigArray([], null);
        $this->assertArrayHasKey($keyExpected, $array, 'Did not find the expected array key: ' . $keyExpected);
        $this->assertArrayNotHasKey($keyAbsent, $array, 'Should not have found the array key; ' . $keyAbsent);
    }

    /**
     * @return array
     */
    public function dataProviderProcessConfigArray()
    {
        return [
            [Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL, 'before', 'after'],
            [Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL, 'after', 'before'],
            [Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL, 'after', 'before'],
            [Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL, 'after', 'before']
        ];
    }

    /**
     * Tests the specific method
     *
     * @param array $itemData
     * @param array $addressData
     *
     * @dataProvider dataProviderMapQuoteExtraTaxablesArray
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMapQuoteExtraTaxables($itemData, $addressData)
    {
        $objectManager = new ObjectManager($this);
        $taxTotalsCalcModel = $objectManager->getObject(Tax::class);
        $taxClassKeyDataObjectMock = $this->getMockForAbstractClass(TaxClassKeyInterface::class);
        $taxClassKeyDataObjectFactoryMock = $this->getMockBuilder(TaxClassKeyInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $taxClassKeyDataObjectFactoryMock->method('create')
            ->willReturn($taxClassKeyDataObjectMock);
        $taxClassKeyDataObjectMock->method('setType')
            ->willReturnSelf();
        $taxClassKeyDataObjectMock->method('setValue')
            ->willReturnSelf();

        $itemDataObjectMock = $this->getMockBuilder(QuoteDetailsItemInterface::class)
            ->setMethods(['getAssociatedTaxables'])
            ->getMockForAbstractClass();
        $itemDataObjectFactoryMock = $this->getMockBuilder(QuoteDetailsItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemDataObjectFactoryMock->method('create')
            ->willReturn($itemDataObjectMock);
        $itemDataObjectMock->method('setTaxClassKey')
            ->willReturnSelf();
        $itemDataObjectMock->method('getAssociatedTaxables')
            ->willReturnSelf();

        $regionFactory = $this->getMockBuilder(RegionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRegionId', 'create'])
            ->getMock();

        $addressFactory = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionBuilder', 'create'])
            ->getMock();
        $region = $this->getMockForAbstractClass(RegionInterface::class, [], '', false);
        $regionFactory->method('setRegionId')
            ->willReturn($regionFactory);
        $regionFactory->method('create')
            ->willReturn($region);
        $addressFactory->method('getRegionBuilder')
            ->willReturn($regionFactory);

        $product = $this->createMock(Product::class);
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getHasChildren', 'getProduct', 'getQuote', 'getCode'])
            ->getMock();
        $item->method('getParentItem')
            ->willReturn(null);
        $item->method('getHasChildren')
            ->willReturn(false);
        $item->method('getCode')
            ->willReturn("1");
        $item->method('getProduct')
            ->willReturn($product);

        foreach ($itemData as $key => $value) {
            $item->setData($key, $value);
        }

        $items = [$item];
        $quote = $this->createMock(Quote::class);

        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getQuote',
                    'getRegionId',
                    'getCustomAttributesCodes'
                ]
            )
            ->addMethods(
                [
                    'getAssociatedTaxables',
                    'getBillingAddress'
                ]
            )
            ->getMock();
        $address->method('getCustomAttributesCodes')
            ->willReturn([]);

        $quote->method('getBillingAddress')
            ->willReturn($address);

        $addressData["cached_items_all"] = $items;
        foreach ($addressData as $key => $value) {
            $address->setData($key, $value);
        }
        $result = $taxTotalsCalcModel->mapQuoteExtraTaxables($itemDataObjectFactoryMock, $address, false);
        $this->assertNotNull($result);
    }

    /*
     * @return array
     */
    /**
     * @return array
     */
    public function dataProviderMapQuoteExtraTaxablesArray()
    {
        $data = [
            'default' => [
                'itemData' => [
                    "qty" => 1, "price" => 100, "tax_percent" => 20, "product_type" => "simple",
                    "code" => "sequence-1", "tax_calculation_item_id" => "sequence-1",
                ],
                'addressData' => [
                    "address_id" => 2, "address_type" => "shipping", "street" => "123 Main Street",
                    "city" => "New York", "region" => "New York", "region_id" => "43", "postcode" => "10012",
                    "country_id" => "US", "telephone" => "111-111-1111", "same_as_billing" => "1",
                    "shipping_method" => "freeshipping_freeshipping", "weight" => 1, "shipping_amount" => 0,
                    "base_shipping_amount" => 0,
                ],
            ],
        ];

        return $data;
    }

    /**
     * Tests the specific method
     *
     * @param string $appliedTaxesData
     * @param array $addressData
     *
     * @dataProvider dataProviderFetchArray
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFetch($appliedTaxesData, $addressData)
    {
        $taxAmount = 8;
        $totalsMock = $this->getMockBuilder(Total::class)
            ->addMethods(['getAppliedTaxes', 'getGrandTotal', 'getSubtotalInclTax'])
            ->onlyMethods(['getTotalAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $taxConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['displayCartTaxWithGrandTotal', 'displayCartZeroTax', 'displayCartSubtotalBoth'])
            ->getMock();
        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        $shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shippingMock);
        $taxConfig
            ->expects($this->once())->method('displayCartTaxWithGrandTotal')
            ->willReturn(true);
        $taxConfig
            ->expects($this->once())->method('displayCartSubtotalBoth')
            ->willReturn(true);

        $objectManager = new ObjectManager($this);

        $serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects($this->any())->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $serializer->expects($this->any())->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        /** @var \Magento\Tax\Model\Sales\Total\Quote\Tax $taxTotalsCalcModel */
        $taxTotalsCalcModel = $objectManager->getObject(
            Tax::class,
            [
                'taxConfig' => $taxConfig,
                'serializer' => $serializer
            ]
        );

        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['convertPrice'])
            ->getMock();
        $quote = $this->createMock(Quote::class);
        $items = [];

        $address = $this->getMockBuilder(Address::class)
            ->addMethods(['getGrandTotal', 'getTaxAmount'])
            ->onlyMethods(['getQuote', 'getAllItems', 'addTotal', 'getCustomAttributesCodes'])
            ->disableOriginalConstructor()
            ->getMock();
        $shippingMock->expects($this->any())->method('getAddress')->willReturn($address);
        $totalsMock
            ->expects($this->once())->method('getAppliedTaxes')
            ->willReturn($appliedTaxesData);
        $totalsMock->method('getGrandTotal')
            ->willReturn(88);
        $quote->method('getStore')
            ->willReturn($store);
        $quote->expects($this->any())->method('getAllAddresses')
            ->willReturn([$address]);
        $address->method('getQuote')
            ->willReturn($quote);
        $address->method('getTaxAmount')
            ->willReturn($taxAmount);
        $address->method('getCustomAttributesCodes')
            ->willReturn([]);

        $addressData["cached_items_all"] = $items;
        foreach ($addressData as $key => $value) {
            $address->setData($key, $value);
        }

        $this->assertNull($totalsMock->getTaxAmount());
        $totalsArray = $taxTotalsCalcModel->fetch($quote, $totalsMock);
        $this->assertArrayHasKey('value', $totalsArray[0]);
        $this->assertEquals($taxAmount, $totalsArray[0]['value']);
        $this->assertEquals(json_decode($appliedTaxesData, true), $totalsArray[0]['full_info']);
    }

    /**
     * @return array
     */
    /*
     * @return array
     */
    public function dataProviderFetchArray()
    {
        $appliedDataString = [
            'amount' => 80.0,
            'base_amount' => 80.0,
            'percent' => 10.0,
            'id' => 'TX Rate',
            'rates' => [
                0 => [
                    'percent' => 10.0,
                    'code' => 'TX Rate',
                    'title' => 'TX Rate',
                ],
            ],
            'item_id' => '1',
            'item_type' => 'product',
            'associated_item_id' => null,
            'process' => 0,
        ];

        $appliedDataString = json_encode($appliedDataString);

        $data = [
            'default' => [
                'appliedTaxesData' => $appliedDataString,
                'addressData' => [
                    "address_id" => 2, "address_type" => "shipping", "street" => "123 Main Street",
                    "city" => "New York", "region" => "New York", "region_id" => "43", "postcode" => "10012",
                    "country_id" => "US", "telephone" => "111-111-1111", "same_as_billing" => "1",
                    "shipping_method" => "freeshipping_freeshipping", "weight" => 1, "shipping_amount" => 0,
                    "base_shipping_amount" => 0,
                ],
            ],
        ];

        return $data;
    }

    /**
     * Tests the specific method
     */
    public function testGetLabel()
    {
        $objectManager = new ObjectManager($this);
        $taxTotalsCalcModel = $objectManager->getObject(Tax::class);
        $this->assertEquals($taxTotalsCalcModel->getLabel(), __('Tax'));
    }

    /**
     * Test the case when address does not have any items
     * Verify that fields in address are reset
     *
     * @return void
     */
    public function testEmptyAddress()
    {
        $totalsMock = $this->createMock(Total::class);
        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $quote = $this->createMock(Quote::class);
        $shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        $shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shippingMock);
        /** @var $address \Magento\Quote\Model\Quote\Address|MockObject */
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAllItems',
                    '__wakeup',
                ]
            )->getMock();
        $shippingMock->expects($this->any())->method('getAddress')->willReturn($address);
        $totalsMock->setTotalAmount('subtotal', 1);
        $totalsMock->setBaseTotalAmount('subtotal', 1);
        $totalsMock->setTotalAmount('tax', 1);
        $totalsMock->setBaseTotalAmount('tax', 1);
        $totalsMock->setTotalAmount('discount_tax_compensation', 1);
        $totalsMock->setBaseTotalAmount('discount_tax_compensation', 1);
        $totalsMock->setTotalAmount('shipping_discount_tax_compensation', 1);
        $totalsMock->setBaseTotalAmount('shipping_discount_tax_compensation', 1);
        $totalsMock->setSubtotalInclTax(1);
        $totalsMock->setBaseSubtotalInclTax(1);

        $shippingAssignmentMock->expects($this->once())->method('getItems')
            ->willReturn([]);

        $objectManager = new ObjectManager($this);
        $taxCollector = $objectManager->getObject(Tax::class);
        $taxCollector->collect($quote, $shippingAssignmentMock, $totalsMock);

        $this->assertEquals(0, $address->getTotalAmount('subtotal'));
        $this->assertEquals(0, $address->getTotalAmount('tax'));
        $this->assertEquals(0, $address->getTotalAmount('discount_tax_compensation'));
        $this->assertEquals(0, $address->getTotalAmount('shipping_discount_tax_compensation'));
        $this->assertEquals(0, $address->getBaseTotalAmount('subtotal'));
        $this->assertEquals(0, $address->getBaseTotalAmount('tax'));
        $this->assertEquals(0, $address->getBaseTotalAmount('discount_tax_compensation'));
        $this->assertEquals(0, $address->getBaseTotalAmount('shipping_discount_tax_compensation'));
        $this->assertEquals(0, $address->getSubtotalInclTax());
        $this->assertEquals(0, $address->getBaseSubtotalInclTax());
    }
}
