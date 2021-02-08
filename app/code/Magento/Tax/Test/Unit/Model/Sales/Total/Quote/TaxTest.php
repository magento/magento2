<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use \Magento\Tax\Model\Sales\Total\Quote\Tax;

/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Tax
 */
use Magento\Tax\Model\Calculation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxTest extends \PHPUnit\Framework\TestCase
{
    const TAX = 0.2;

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
        $this->markTestIncomplete('Source code is not testable. Need to be refactored before unit testing');
        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $totalsMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Total::class);
        $objectManager = new ObjectManager($this);
        $taxData = $this->createMock(\Magento\Tax\Helper\Data::class);
        $taxConfig = $this->getMockBuilder(\Magento\Tax\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['priceIncludesTax', 'getShippingTaxClass', 'shippingPriceIncludesTax', 'discountTax'])
            ->getMock();
        $taxConfig
            ->expects($this->any())
            ->method('priceIncludesTax')
            ->willReturn(false);
        $taxConfig->expects($this->any())
            ->method('getShippingTaxClass')
            ->willReturn(1);
        $taxConfig->expects($this->any())
            ->method('shippingPriceIncludesTax')
            ->willReturn(false);
        $taxConfig->expects($this->any())
            ->method('discountTax')
            ->willReturn(false);

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getHasChildren', 'getProduct', 'getQuote', 'getCode', '__wakeup'])
            ->getMock();
        $item
            ->expects($this->any())
            ->method('getParentItem')
            ->willReturn(null);
        $item
            ->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(false);
        $item
            ->expects($this->any())
            ->method('getCode')
            ->willReturn("1");
        $item
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);

        foreach ($itemData as $key => $value) {
            $item->setData($key, $value);
        }

        $items = [$item];
        $taxDetails = $this->createMock(\Magento\Tax\Api\Data\TaxDetailsInterface::class);
        $taxDetails->expects($this->any())
            ->method('getItems')
            ->willReturn($items);

        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'hasSingleStore', 'isSingleStoreMode', 'getStores', 'getWebsite', 'getWebsites',
                'reinitStores', 'getDefaultStoreView', 'setIsSingleStoreModeAllowed', 'getGroup', 'getGroups',
                'clearWebsiteCache', 'setCurrentStore', ])
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $calculatorFactory = $this->getMockBuilder(\Magento\Tax\Model\Calculation\CalculatorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $calculationTool = $this->getMockBuilder(\Magento\Tax\Model\Calculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRate', 'getAppliedRates', 'round', 'calcTaxAmount', '__wakeup'])
            ->getMock();
        $calculationTool->expects($this->any())
            ->method('round')
            ->willReturnArgument(0);
        $calculationTool->expects($this->any())
            ->method('getRate')
            ->willReturn(20);
        $calculationTool->expects($this->any())
            ->method('calcTaxAmount')
            ->willReturn(20);

        $calculationTool->expects($this->any())
            ->method('getAppliedRates')
            ->willReturn($appliedRatesData);
        $calculator = $objectManager->getObject(
            \Magento\Tax\Model\Calculation\TotalBaseCalculator::class,
            [
                'calculationTool' => $calculationTool,
            ]
        );
        $calculatorFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($calculator);

        $taxCalculationService = $this->createMock(\Magento\Tax\Api\TaxCalculationInterface::class);

        $taxClassKeyDataObjectMock = $this->createMock(\Magento\Tax\Api\Data\TaxClassKeyInterface::class);
        $taxClassKeyDataObjectFactoryMock = $this->getMockBuilder(
            \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $taxClassKeyDataObjectFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($taxClassKeyDataObjectMock);
        $taxClassKeyDataObjectMock
            ->expects($this->any())
            ->method('setType')
            ->willReturnSelf();
        $taxClassKeyDataObjectMock
            ->expects($this->any())
            ->method('setValue')
            ->willReturnSelf();

        $itemDataObjectMock = $this->createMock(\Magento\Tax\Api\Data\QuoteDetailsItemInterface::class);
        $itemDataObjectFactoryMock = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $itemDataObjectFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($itemDataObjectMock);
        $itemDataObjectMock
            ->expects($this->any())
            ->method('setTaxClassKey')
            ->willReturnSelf();
        $itemDataObjectMock
            ->expects($this->any())
            ->method('getAssociatedTaxables')
            ->willReturnSelf();

        $regionFactory = $this->getMockBuilder(\Magento\Customer\Api\Data\RegionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRegionId', 'create'])
            ->getMock();

        $addressFactory = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionBuilder', 'create'])
            ->getMock();
        $region = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\RegionInterface::class, [], '', false);
        $regionFactory
            ->expects($this->any())
            ->method('setRegionId')
            ->willReturn($regionFactory);
        $regionFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($region);
        $addressFactory
            ->expects($this->any())
            ->method('getRegionBuilder')
            ->willReturn($regionFactory);

        $quoteDetails = $this->createMock(\Magento\Tax\Api\Data\QuoteDetailsInterface::class);
        $quoteDetailsDataObjectFactoryMock = $this->createPartialMock(
            \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory::class,
            ['create']
        );
        $quoteDetailsDataObjectFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($quoteDetails);

        $quoteDetailsItemDataObjectFactoryMock = $this->createPartialMock(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory::class,
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

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['convertPrice', '__wakeup', 'getStoreId'])
            ->getMock();
        $store
            ->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quote
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAssociatedTaxables',
                'getQuote', 'getBillingAddress', 'getRegionId',
                '__wakeup', 'getCustomAttributesCodes'])
            ->getMock();
        $item
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);
        $address
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);
        $address
            ->expects($this->any())
            ->method('getAssociatedTaxables')
            ->willReturn([]);
        $address
            ->expects($this->any())
            ->method('getRegionId')
            ->willReturn($region);
        $address
            ->expects($this->any())
            ->method('getCustomAttributesCodes')
            ->willReturn([]);
        $quote
            ->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($address);
        $addressFactory
            ->expects($this->any())
            ->method('create')
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
        $taxData = $this->createMock(\Magento\Tax\Helper\Data::class);
        $taxData
            ->expects($this->any())
            ->method('getCalculationSequence')
            ->willReturn($calculationSequence);

        $objectManager = new ObjectManager($this);
        $taxTotalsCalcModel = $objectManager->getObject(
            \Magento\Tax\Model\Sales\Total\Quote\Tax::class,
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
        $taxTotalsCalcModel = $objectManager->getObject(\Magento\Tax\Model\Sales\Total\Quote\Tax::class);
        $taxClassKeyDataObjectMock = $this->createMock(\Magento\Tax\Api\Data\TaxClassKeyInterface::class);
        $taxClassKeyDataObjectFactoryMock = $this->getMockBuilder(
            \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $taxClassKeyDataObjectFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($taxClassKeyDataObjectMock);
        $taxClassKeyDataObjectMock
            ->expects($this->any())
            ->method('setType')
            ->willReturnSelf();
        $taxClassKeyDataObjectMock
            ->expects($this->any())
            ->method('setValue')
            ->willReturnSelf();

        $itemDataObjectMock = $this->getMockBuilder(\Magento\Tax\Api\Data\QuoteDetailsItemInterface::class)
            ->setMethods(['getAssociatedTaxables'])
            ->getMockForAbstractClass();
        $itemDataObjectFactoryMock = $this->getMockBuilder(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $itemDataObjectFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($itemDataObjectMock);
        $itemDataObjectMock
            ->expects($this->any())
            ->method('setTaxClassKey')
            ->willReturnSelf();
        $itemDataObjectMock
            ->expects($this->any())
            ->method('getAssociatedTaxables')
            ->willReturnSelf();

        $regionFactory = $this->getMockBuilder(\Magento\Customer\Api\Data\RegionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRegionId', 'create'])
            ->getMock();

        $addressFactory = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionBuilder', 'create'])
            ->getMock();
        $region = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\RegionInterface::class, [], '', false);
        $regionFactory
            ->expects($this->any())
            ->method('setRegionId')
            ->willReturn($regionFactory);
        $regionFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($region);
        $addressFactory
            ->expects($this->any())
            ->method('getRegionBuilder')
            ->willReturn($regionFactory);

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getHasChildren', 'getProduct', 'getQuote', 'getCode', '__wakeup'])
            ->getMock();
        $item
            ->expects($this->any())
            ->method('getParentItem')
            ->willReturn(null);
        $item
            ->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(false);
        $item
            ->expects($this->any())
            ->method('getCode')
            ->willReturn("1");
        $item
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);

        foreach ($itemData as $key => $value) {
            $item->setData($key, $value);
        }

        $items = [$item];
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);

        $address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAssociatedTaxables',
                    'getQuote',
                    'getBillingAddress',
                    'getRegionId',
                    'getCustomAttributesCodes',
                    '__wakeup'
                ]
            )
            ->getMock();
        $address
            ->expects($this->any())
            ->method('getCustomAttributesCodes')
            ->willReturn([]);

        $quote
            ->expects($this->any())
            ->method('getBillingAddress')
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
        $methods = ['getAppliedTaxes', 'getTotalAmount', 'getGrandTotal', 'getSubtotalInclTax'];
        $totalsMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Total::class, $methods);
        $taxConfig = $this->getMockBuilder(\Magento\Tax\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['displayCartTaxWithGrandTotal', 'displayCartZeroTax', 'displayCartSubtotalBoth'])
            ->getMock();
        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $shippingMock = $this->createMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shippingMock);
        $taxConfig
            ->expects($this->once())
            ->method('displayCartTaxWithGrandTotal')
            ->willReturn(true);
        $taxConfig
            ->expects($this->once())
            ->method('displayCartSubtotalBoth')
            ->willReturn(true);

        $objectManager = new ObjectManager($this);

        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        /** @var \Magento\Tax\Model\Sales\Total\Quote\Tax $taxTotalsCalcModel */
        $taxTotalsCalcModel = $objectManager->getObject(
            \Magento\Tax\Model\Sales\Total\Quote\Tax::class,
            [
                'taxConfig' => $taxConfig,
                'serializer' => $serializer
            ]
        );

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['convertPrice', '__wakeup'])
            ->getMock();
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $items = [];

        $address = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, [
                'getQuote', 'getAllItems', 'getGrandTotal', '__wakeup',
                'addTotal', 'getTaxAmount', 'getCustomAttributesCodes'
            ]);
        $shippingMock->expects($this->any())->method('getAddress')->willReturn($address);
        $totalsMock
            ->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxesData);
        $totalsMock
            ->expects($this->any())
            ->method('getGrandTotal')
            ->willReturn(88);
        $quote
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $quote->expects($this->any())
            ->method('getAllAddresses')
            ->willReturn([$address]);
        $address
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);
        $address
            ->expects($this->any())
            ->method('getTaxAmount')
            ->willReturn($taxAmount);
        $address
            ->expects($this->any())
            ->method('getCustomAttributesCodes')
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
        $taxTotalsCalcModel = $objectManager->getObject(\Magento\Tax\Model\Sales\Total\Quote\Tax::class);
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
        $totalsMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Total::class);
        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $shippingMock = $this->createMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shippingMock);
        /** @var $address \Magento\Quote\Model\Quote\Address|PHPUnit\Framework\MockObject\MockObject */
        $address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
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

        $shippingAssignmentMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $objectManager = new ObjectManager($this);
        $taxCollector = $objectManager->getObject(\Magento\Tax\Model\Sales\Total\Quote\Tax::class);
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
