<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Tax
 */
use Magento\Tax\Model\Calculation;
use Magento\TestFramework\Helper\ObjectManager;

class TaxTest extends \PHPUnit_Framework_TestCase
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
     */
    public function testCollect($itemData, $appliedRatesData, $taxDetailsData, $quoteDetailsData,
        $addressData, $verifyData
    ) {
        $this->markTestIncomplete('Source code is not testable. Need to be refactored before unit testing');
        $objectManager = new ObjectManager($this);
        $taxData = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $taxConfig = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['priceIncludesTax', 'getShippingTaxClass', 'shippingPriceIncludesTax', 'discountTax'])
            ->getMock();
        $taxConfig
            ->expects($this->any())
            ->method('priceIncludesTax')
            ->will($this->returnValue(false));
        $taxConfig->expects($this->any())
            ->method('getShippingTaxClass')
            ->will($this->returnValue(1));
        $taxConfig->expects($this->any())
            ->method('shippingPriceIncludesTax')
            ->will($this->returnValue(false));
        $taxConfig->expects($this->any())
            ->method('discountTax')
            ->will($this->returnValue(false));

        $product = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $item = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getHasChildren', 'getProduct', 'getQuote', 'getCode', '__wakeup'])
            ->getMock();
        $item
            ->expects($this->any())
            ->method('getParentItem')
            ->will($this->returnValue(null));
        $item
            ->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(false));
        $item
            ->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue("1"));
        $item
            ->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($product));

        foreach ($itemData as $key => $value) {
            $item->setData($key, $value);
        }

        $items = [$item];
        $taxDetails = $this->getMock('Magento\Tax\Api\Data\TaxDetailsInterface');
        $taxDetails->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue($items));

        $storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'hasSingleStore', 'isSingleStoreMode', 'getStores', 'getWebsite', 'getWebsites',
                'reinitStores', 'getDefaultStoreView', 'setIsSingleStoreModeAllowed', 'getGroup', 'getGroups',
                'clearWebsiteCache', 'setCurrentStore', ])
            ->getMock();
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $calculatorFactory = $this->getMockBuilder('Magento\Tax\Model\Calculation\CalculatorFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $calculationTool = $this->getMockBuilder('Magento\Tax\Model\Calculation')
            ->disableOriginalConstructor()
            ->setMethods(['getRate', 'getAppliedRates', 'round', 'calcTaxAmount', '__wakeup'])
            ->getMock();
        $calculationTool->expects($this->any())
            ->method('round')
            ->will($this->returnArgument(0));
        $calculationTool->expects($this->any())
            ->method('getRate')
            ->will($this->returnValue(20));
        $calculationTool->expects($this->any())
            ->method('calcTaxAmount')
            ->will($this->returnValue(20));

        $calculationTool->expects($this->any())
            ->method('getAppliedRates')
            ->will($this->returnValue($appliedRatesData));
        $calculator = $objectManager->getObject('Magento\Tax\Model\Calculation\TotalBaseCalculator',
            [
                'calculationTool' => $calculationTool,
            ]
        );
        $calculatorFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($calculator));

        $taxCalculationService = $this->getMock('\Magento\Tax\Api\TaxCalculationInterface');

        $taxClassKeyBuilder = $this->getMockBuilder('\Magento\Tax\Api\Data\TaxClassKeyDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setType', 'setValue', 'create'])
            ->getMock();
        $taxClassKeyBuilder
            ->expects($this->any())
            ->method('setType')
            ->will($this->returnValue($taxClassKeyBuilder));
        $taxClassKeyBuilder
            ->expects($this->any())
            ->method('setValue')
            ->will($this->returnValue($taxClassKeyBuilder));
        $taxClassKeyBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($taxClassKeyBuilder));

        $itemBuilder = $this->getMockBuilder('\Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['getTaxClassKeyBuilder', 'create', 'setTaxClassKey', 'getAssociatedTaxables'])
            ->getMock();
        $itemBuilder
            ->expects($this->any())
            ->method('getTaxClassKeyBuilder')
            ->will($this->returnValue($taxClassKeyBuilder));
        $itemBuilder
            ->expects($this->any())
            ->method('setTaxClassKey')
            ->will($this->returnValue($itemBuilder));
        $itemBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($items));
        $itemBuilder
            ->expects($this->any())
            ->method('getAssociatedTaxables')
            ->will($this->returnValue(null));

        $regionBuilder = $this->getMockBuilder('Magento\Customer\Api\Data\RegionDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setRegionId', 'create'])
            ->getMock();

        $addressBuilder = $this->getMockBuilder('Magento\Customer\Api\Data\AddressDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['getRegionBuilder', 'create'])
            ->getMock();
        $region = $this->getMockForAbstractClass('Magento\Customer\Api\Data\RegionInterface', [], '', false);
        $regionBuilder
            ->expects($this->any())
            ->method('setRegionId')
            ->will($this->returnValue($regionBuilder));
        $regionBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($region));
        $addressBuilder
            ->expects($this->any())
            ->method('getRegionBuilder')
            ->will($this->returnValue($regionBuilder));

        $quoteDetails = $this->getMock('Magento\Tax\Api\Data\QuoteDetailsInterface');
        $quoteDetailsBuilder = $this->getMock('\Magento\Tax\Api\Data\QuoteDetailsDataBuilder',
            [
                'getItemBuilder', 'getAddressBuilder', 'getTaxClassKeyBuilder', 'create', 'setBillingAddress',
                'setShippingAddress', 'setCustomerTaxClassKey', 'setItems', ''
            ],
            [],
            '',
            false
        );
        $quoteDetailsBuilder
            ->expects($this->any())
            ->method('getItemBuilder')
            ->will($this->returnValue($itemBuilder));
        $quoteDetailsBuilder
            ->expects($this->any())
            ->method('getAddressBuilder')
            ->will($this->returnValue($addressBuilder));
        $quoteDetailsBuilder
            ->expects($this->any())
            ->method('getTaxClassKeyBuilder')
            ->will($this->returnValue($taxClassKeyBuilder));
        $quoteDetailsBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($quoteDetails));

        $quoteDetailsItemDataBuilder = $this->getMock(
            'Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder',
            [
                'setCode', 'setQuantity', 'setTaxClassKey', 'setTaxIncluded', 'setType',
                'setUnitPrice', 'setDiscountAmount', 'setParentCode', 'create'
            ],
            [],
            '',
            false
        );

        $taxClassKeyDataBuilder =  $this->getMock(
            'Magento\Tax\Api\Data\TaxClassKeyDataBuilder',
            ['setType', 'setValue', 'create'],
            [],
            '',
            false
        );

        $taxTotalsCalcModel = new Tax(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsBuilder,
            $quoteDetailsItemDataBuilder,
            $taxClassKeyDataBuilder,
            $addressBuilder,
            $taxData
        );

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['convertPrice', '__wakeup', 'getStoreId'])
            ->getMock();
        $store
            ->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));
        $quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quote
            ->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));
        $address = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getAssociatedTaxables',
                          'getQuote', 'getBillingAddress', 'getRegionId',
                          '__wakeup', ])
            ->getMock();
        $item
            ->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));
        $address
            ->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));
        $address
            ->expects($this->any())
            ->method('getAssociatedTaxables')
            ->will($this->returnValue([]));
        $address
            ->expects($this->any())
            ->method('getRegionId')
            ->will($this->returnValue($region));
        $quote
            ->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($address));
        $addressBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($address));

        $addressData["cached_items_all"] = $items;
        foreach ($addressData as $key => $value) {
            $address->setData($key, $value);
        }

        $taxTotalsCalcModel->collect($address);
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
                            'tax_included = false',
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
        $taxData = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $taxData
            ->expects($this->any())
            ->method('getCalculationSequence')
            ->will($this->returnValue($calculationSequence));

        $objectManager = new ObjectManager($this);
        $taxTotalsCalcModel = $objectManager->getObject(
            'Magento\Tax\Model\Sales\Total\Quote\Tax',
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
        $taxTotalsCalcModel = $objectManager->getObject('Magento\Tax\Model\Sales\Total\Quote\Tax');

        $taxClassKeyBuilder = $this->getMockBuilder('\Magento\Tax\Api\Data\TaxClassKeyDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setType', 'setValue', 'create'])
            ->getMock();
        $taxClassKeyBuilder
            ->expects($this->any())
            ->method('setType')
            ->will($this->returnValue($taxClassKeyBuilder));
        $taxClassKeyBuilder
            ->expects($this->any())
            ->method('setValue')
            ->will($this->returnValue($taxClassKeyBuilder));
        $taxClassKeyBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($taxClassKeyBuilder));

        $itemBuilder = $this->getMockBuilder('\Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['getTaxClassKeyBuilder', 'setTaxClassKey', 'create', 'getAssociatedTaxables'])
            ->getMock();
        $itemBuilder
            ->expects($this->any())
            ->method('getTaxClassKeyBuilder')
            ->will($this->returnValue($taxClassKeyBuilder));
        $itemBuilder
            ->expects($this->any())
            ->method('setTaxClassKey')
            ->will($this->returnValue($itemBuilder));
        $itemBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($itemBuilder));
        $itemBuilder
            ->expects($this->any())
            ->method('getAssociatedTaxables')
            ->will($this->returnValue(null));

        $regionBuilder = $this->getMockBuilder('Magento\Customer\Api\Data\RegionDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setRegionId', 'create'])
            ->getMock();

        $addressBuilder = $this->getMockBuilder('Magento\Customer\Api\Data\AddressDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['getRegionBuilder', 'create'])
            ->getMock();
        $region = $this->getMockForAbstractClass('Magento\Customer\Api\Data\RegionInterface', [], '', false);
        $regionBuilder
            ->expects($this->any())
            ->method('setRegionId')
            ->will($this->returnValue($regionBuilder));
        $regionBuilder
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($region));
        $addressBuilder
            ->expects($this->any())
            ->method('getRegionBuilder')
            ->will($this->returnValue($regionBuilder));

        $product = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $item = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getHasChildren', 'getProduct', 'getQuote', 'getCode', '__wakeup'])
            ->getMock();
        $item
            ->expects($this->any())
            ->method('getParentItem')
            ->will($this->returnValue(null));
        $item
            ->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(false));
        $item
            ->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue("1"));
        $item
            ->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($product));

        foreach ($itemData as $key => $value) {
            $item->setData($key, $value);
        }

        $items = [$item];
        $quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);

        $address = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getAssociatedTaxables', 'getQuote', 'getBillingAddress', 'getRegionId', '__wakeup'])
            ->getMock();
        $quote
            ->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($address));

        $addressData["cached_items_all"] = $items;
        foreach ($addressData as $key => $value) {
            $address->setData($key, $value);
        }
        $taxTotalsCalcModel->mapQuoteExtraTaxables($itemBuilder, $address, false);
    }

    /*
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
     * @param string $itemData
     * @param array $addressData
     *
     * @dataProvider dataProviderFetchArray
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFetch($appliedTaxesData, $addressData)
    {
        $taxConfig = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['displayCartTaxWithGrandTotal', 'displayCartZeroTax', 'displayCartSubtotalBoth'])
            ->getMock();
        $taxConfig
            ->expects($this->once())
            ->method('displayCartTaxWithGrandTotal')
            ->will($this->returnValue(true));
        $taxConfig
            ->expects($this->once())
            ->method('displayCartSubtotalBoth')
            ->will($this->returnValue(true));

        $objectManager = new ObjectManager($this);
        $taxTotalsCalcModel = $objectManager->getObject(
            'Magento\Tax\Model\Sales\Total\Quote\Tax',
            ['taxConfig' => $taxConfig]
        );

        $appliedTaxes = unserialize($appliedTaxesData);
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['convertPrice', '__wakeup'])
            ->getMock();
        $quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $items = [];

        $address = $this->getMock(
            '\Magento\Sales\Model\Quote\Address',
            [
                'getAppliedTaxes', 'getQuote', 'getAllItems', 'getGrandTotal', '__wakeup',
                'addTotal', 'getTaxAmount'
            ],
            [],
            '',
            false
        );
        $address
            ->expects($this->once())
            ->method('getAppliedTaxes')
            ->will($this->returnValue($appliedTaxes));
        $address
            ->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));
        $address
            ->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue($items));
        $address
            ->expects($this->any())
            ->method('getGrandTotal')
            ->will($this->returnValue(88));
        $quote
            ->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));
        $address
            ->expects($this->any())
            ->method('getTaxAmount')
            ->will($this->returnValue(8));

        $addressData["cached_items_all"] = $items;
        foreach ($addressData as $key => $value) {
            $address->setData($key, $value);
        }

        $taxTotalsCalcModel->fetch($address);
    }

    /**
     * @return array
     */
    /*
     * @return array
     */
    public function dataProviderFetchArray()
    {
        $appliedDataString = 'a:1:{s:7:"TX Rate";a:9:{s:6:"amount";d:80;s:11:"base_amount";d:80;s:7:"percent";';
        $appliedDataString .= 'd:10;s:2:"id";s:7:"TX Rate";s:5:"rates";a:1:{i:0;a:3:{s:7:"percent";d:10;s:4:"code";';
        $appliedDataString .= 's:7:"TX Rate";s:5:"title";s:7:"TX Rate";}}s:7:"item_id";s:1:"1";s:9:"item_type";';
        $appliedDataString .= 's:7:"product";s:18:"associated_item_id";N;s:7:"process";i:0;}}';
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
        $taxTotalsCalcModel = $objectManager->getObject('Magento\Tax\Model\Sales\Total\Quote\Tax');
        $this->assertSame($taxTotalsCalcModel->getLabel(), __('Tax'));
    }

    /**
     * Test the case when address does not have any items
     * Verify that fields in address are reset
     *
     * @return void
     */
    public function testEmptyAddress()
    {
        /** @var $address \Magento\Sales\Model\Quote\Address|PHPUnit_Framework_MockObject_MockObject */
        $address = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAllItems',
                    '__wakeup',
                ]
            )->getMock();

        $address->setTotalAmount('subtotal', 1);
        $address->setBaseTotalAmount('subtotal', 1);
        $address->setTotalAmount('tax', 1);
        $address->setBaseTotalAmount('tax', 1);
        $address->setTotalAmount('hidden_tax', 1);
        $address->setBaseTotalAmount('hidden_tax', 1);
        $address->setTotalAmount('shipping_hidden_tax', 1);
        $address->setBaseTotalAmount('shipping_hidden_tax', 1);
        $address->setSubtotalInclTax(1);
        $address->setBaseSubtotalInclTax(1);

        $address->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue([]));

        $objectManager = new ObjectManager($this);
        $taxCollector = $objectManager->getObject('Magento\Tax\Model\Sales\Total\Quote\Tax');
        $taxCollector->collect($address);

        $this->assertEquals(0, $address->getTotalAmount('subtotal'));
        $this->assertEquals(0, $address->getTotalAmount('tax'));
        $this->assertEquals(0, $address->getTotalAmount('hidden_tax'));
        $this->assertEquals(0, $address->getTotalAmount('shipping_hidden_tax'));
        $this->assertEquals(0, $address->getBaseTotalAmount('subtotal'));
        $this->assertEquals(0, $address->getBaseTotalAmount('tax'));
        $this->assertEquals(0, $address->getBaseTotalAmount('hidden_tax'));
        $this->assertEquals(0, $address->getBaseTotalAmount('shipping_hidden_tax'));
        $this->assertEquals(0, $address->getSubtotalInclTax());
        $this->assertEquals(0, $address->getBaseSubtotalInclTax());
    }
}
