<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Model\Total\Quote;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Weee\Model\Total\Quote\Weee;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WeeeTest extends TestCase
{
    /**
     * @var MockObject|PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Weee\Model\Total\Quote\Weee
     */
    protected $weeeCollector;

    private $serializerMock;

    /**
     * Setup tax helper with an array of methodName, returnValue
     *
     * @param array $taxConfig
     * @return MockObject|\Magento\Tax\Helper\Data
     */
    protected function setupTaxHelper($taxConfig)
    {
        $taxHelper = $this->createMock(Data::class);

        foreach ($taxConfig as $method => $value) {
            $taxHelper->expects($this->any())->method($method)->willReturn($value);
        }

        return $taxHelper;
    }

    /**
     * Setup calculator to return tax rates
     *
     * @param array $taxRates
     * @return MockObject|Calculation
     */
    protected function setupTaxCalculation($taxRates)
    {
        $storeTaxRate = $taxRates['store_tax_rate'];
        $customerTaxRate = $taxRates['customer_tax_rate'];

        $taxCalculation = $this->createPartialMock(
            Calculation::class,
            ['getRateOriginRequest', 'getRateRequest', 'getRate']
        );

        $rateRequest = new DataObject();
        $defaultRateRequest = new DataObject();

        $taxCalculation->expects($this->any())->method('getRateRequest')->willReturn($rateRequest);
        $taxCalculation
            ->expects($this->any())
            ->method('getRateOriginRequest')
            ->willReturn($defaultRateRequest);

        $taxCalculation
            ->expects($this->any())
            ->method('getRate')
            ->will($this->onConsecutiveCalls($storeTaxRate, $customerTaxRate));

        return $taxCalculation;
    }

    /**
     * Setup weee helper with an array of methodName, returnValue
     *
     * @param array $weeeConfig
     * @return MockObject|\Magento\Weee\Helper\Data
     */
    protected function setupWeeeHelper($weeeConfig)
    {
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();

        $weeeHelper = $this->getMockBuilder(\Magento\Weee\Helper\Data::class)
            ->setConstructorArgs(['serializer' => $this->serializerMock])
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($weeeConfig as $method => $value) {
            $weeeHelper->expects($this->any())->method($method)->willReturn($value);
        }

        return $weeeHelper;
    }

    /**
     * Setup the basics of an item mock
     *
     * @param float $itemTotalQty
     * @return MockObject|Item
     */
    protected function setupItemMockBasics($itemTotalQty)
    {
        $itemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getHasChildren'])
            ->onlyMethods(
                [
                    'getProduct',
                    'getQuote',
                    'getAddress',
                    'getTotalQty',
                    'getParentItem',
                    'getChildren',
                    'isChildrenCalculated'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->createMock(Product::class);
        $itemMock->expects($this->any())->method('getProduct')->willReturn($productMock);
        $itemMock->expects($this->any())->method('getTotalQty')->willReturn($itemTotalQty);

        return $itemMock;
    }

    /**
     * Setup an item mock
     *
     * @param float $itemQty
     * @return MockObject|Item
     */
    protected function setupItemMock($itemQty)
    {
        $itemMock = $this->setupItemMockBasics($itemQty);

        $itemMock->expects($this->any())->method('getParentItem')->willReturn(false);
        $itemMock->expects($this->any())->method('getHasChildren')->willReturn(false);
        $itemMock->expects($this->any())->method('getChildren')->willReturn([]);
        $itemMock->expects($this->any())->method('isChildrenCalculated')->willReturn(false);

        return $itemMock;
    }

    /**
     * Setup an item mock as a parent of a child item mock.  Return both.
     *
     * @param float $parentQty
     * @param float $itemQty
     * @return MockObject[]|Item[]
     */
    protected function setupParentItemWithChildrenMock($parentQty, $itemQty)
    {
        $items = [];

        $parentItemMock = $this->setupItemMockBasics($parentQty);

        $childItemMock = $this->setupItemMockBasics($parentQty * $itemQty);
        $childItemMock->expects($this->any())->method('getParentItem')->willReturn($parentItemMock);
        $childItemMock->expects($this->any())->method('getHasChildren')->willReturn(false);
        $childItemMock->expects($this->any())->method('getChildren')->willReturn([]);
        $childItemMock->expects($this->any())->method('isChildrenCalculated')->willReturn(false);

        $parentItemMock->expects($this->any())->method('getParentItem')->willReturn(false);
        $parentItemMock->expects($this->any())->method('getHasChildren')->willReturn(true);
        $parentItemMock->expects($this->any())->method('getChildren')->willReturn([$childItemMock]);
        $parentItemMock->expects($this->any())->method('isChildrenCalculated')->willReturn(true);

        $items[] = $parentItemMock;
        $items[] = $childItemMock;
        return $items;
    }

    /**
     * Setup address mock
     *
     * @param MockObject[]|Item[] $items
     * @return MockObject
     */
    protected function setupAddressMock($items)
    {
        $addressMock = $this->createPartialMock(Address::class, [
            'getAllItems',
            'getQuote',
            'getCustomAttributesCodes'
        ]);

        $quoteMock = $this->createMock(Quote::class);
        $storeMock = $this->createMock(Store::class);
        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();
        $this->priceCurrency->expects($this->any())->method('round')->willReturnArgument(0);
        $this->priceCurrency->expects($this->any())->method('convert')->willReturnArgument(0);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $addressMock->expects($this->any())->method('getAllItems')->willReturn($items);
        $addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $addressMock->expects($this->any())->method('getCustomAttributesCodes')->willReturn([]);

        return $addressMock;
    }

    /**
     * Setup shipping assignment mock.
     * @param MockObject $addressMock
     * @param MockObject $itemMock
     * @return MockObject
     */
    protected function setupShippingAssignmentMock($addressMock, $itemMock)
    {
        $shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        $shippingMock->expects($this->any())->method('getAddress')->willReturn($addressMock);
        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn($itemMock);
        $shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shippingMock);

        return $shippingAssignmentMock;
    }

    /**
     * Verify that correct fields of item has been set
     *
     * @param MockObject|Item $item
     * @param $itemData
     */
    public function verifyItem(Item $item, $itemData)
    {
        foreach ($itemData as $key => $value) {
            $this->assertEquals($value, $item->getData($key), 'item ' . $key . ' is incorrect');
        }
    }

    /**
     * Verify that correct fields of address has been set
     *
     * @param MockObject|Address $address
     * @param $addressData
     */
    public function verifyAddress($address, $addressData)
    {
        foreach ($addressData as $key => $value) {
            $this->assertEquals($value, $address->getData($key), 'address ' . $key . ' is incorrect');
        }
    }

    /**
     * Test the collect function of the weee collector
     *
     * @param array $taxConfig
     * @param array $weeeConfig
     * @param array $taxRates
     * @param array $itemData
     * @param float $itemQty
     * @param float $parentQty
     * @param array $addressData
     * @param bool $assertSetApplied
     * @dataProvider collectDataProvider
     */
    public function testCollect(
        $taxConfig,
        $weeeConfig,
        $taxRates,
        $itemData,
        $itemQty,
        $parentQty,
        $addressData,
        $assertSetApplied = false
    ) {
        $items = [];
        if ($parentQty > 0) {
            $items = $this->setupParentItemWithChildrenMock($parentQty, $itemQty);
        } else {
            $itemMock = $this->setupItemMock($itemQty);
            $items[] = $itemMock;
        }
        $quoteMock = $this->createMock(Quote::class);
        $storeMock = $this->createMock(Store::class);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $addressMock = $this->setupAddressMock($items);
        $totalMock = new Total(
            [],
            $this->getMockBuilder(Json::class)
                ->getMock()
        );
        $shippingAssignmentMock = $this->setupShippingAssignmentMock($addressMock, $items);

        $taxHelper = $this->setupTaxHelper($taxConfig);
        $weeeHelper = $this->setupWeeeHelper($weeeConfig);
        $calculator = $this->setupTaxCalculation($taxRates);

        if ($assertSetApplied) {
            $weeeHelper
                ->expects($this->at(1))
                ->method('setApplied')
                ->with(reset($items), []);

            $weeeHelper
                ->expects($this->at(2))
                ->method('setApplied')
                ->with(end($items), []);

            $weeeHelper
                ->expects($this->at(8))
                ->method('setApplied')
                ->with(end($items), [
                    [
                        'title' => 'Recycling Fee',
                        'base_amount' => '10',
                        'amount' => '10',
                        'row_amount' => '20',
                        'base_row_amount' => '20',
                        'base_amount_incl_tax' => '10',
                        'amount_incl_tax' => '10',
                        'row_amount_incl_tax' => '20',
                        'base_row_amount_incl_tax' => '20',
                    ],
                    [
                        'title' => 'FPT Fee',
                        'base_amount' => '5',
                        'amount' => '5',
                        'row_amount' => '10',
                        'base_row_amount' => '10',
                        'base_amount_incl_tax' => '5',
                        'amount_incl_tax' => '5',
                        'row_amount_incl_tax' => '10',
                        'base_row_amount_incl_tax' => '10',
                    ]
                ]);
        }

        $arguments = [
            'taxData' => $taxHelper,
            'calculation' => $calculator,
            'weeeData' => $weeeHelper,
            'priceCurrency' => $this->priceCurrency
        ];

        $helper = new ObjectManager($this);
        $this->weeeCollector = $helper->getObject(Weee::class, $arguments);

        $this->weeeCollector->collect($quoteMock, $shippingAssignmentMock, $totalMock);

        $this->verifyItem(end($items), $itemData);          // verify the (child) item
        $this->verifyAddress($totalMock, $addressData);
    }

    /**
     * Data provider for testCollect
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * Multiple datasets
     *
     * @return array
     */
    public function collectDataProvider()
    {
        $data = [];

        // 1. This collector never computes tax.  Instead it sets up various fields for the tax calculation.
        // 2. When the Weee is not taxable, this collector will change the address data as follows:
        //     accumulate the totals into 'weee_total_excl_tax' and 'weee_base_total_excl_tax'

        $data['price_incl_tax_weee_taxable_unit_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAlgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
            ],
        ];

        $data['price_incl_tax_weee_taxable_unit_not_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAlgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => false,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
            ],
        ];

        $data['price_excl_tax_weee_taxable_unit_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAlgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
            ],
        ];

        $data['price_incl_tax_weee_non_taxable_unit_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAlgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_total_excl_tax' => 20,
                'weee_base_total_excl_tax' => 20,
            ],
        ];

        $data['price_excl_tax_weee_non_taxable_unit_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAlgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_total_excl_tax' => 20,
                'weee_base_total_excl_tax' => 20,
            ],
        ];

        $data['price_incl_tax_weee_taxable_row_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAlgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
            ],
        ];

        $data['price_excl_tax_weee_taxable_row_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAlgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
            ],
        ];

        $data['price_incl_tax_weee_non_taxable_row_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAlgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_total_excl_tax' => 20,
                'weee_base_total_excl_tax' => 20,
            ],
        ];

        $data['price_excl_tax_weee_non_taxable_row_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAlgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_total_excl_tax' => 20,
                'weee_base_total_excl_tax' => 20,
            ],
        ];

        $data['price_excl_tax_weee_non_taxable_row_not_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAlgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => false,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 20,
                'base_weee_tax_applied_row_amnt' => 20,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'parent_qty' => 0,
            'address_data' => [
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_total_excl_tax' => 20,
                'weee_base_total_excl_tax' => 20,
            ],
        ];

        $data['price_excl_tax_weee_taxable_unit_not_included_in_subtotal_PARENT_ITEM'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAlgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => false,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 10,
                'base_weee_tax_applied_amount' => 10,
                'weee_tax_applied_row_amount' => 60,
                'base_weee_tax_applied_row_amnt' => 60,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 60,
                'base_weee_tax_applied_row_amnt_incl_tax' => 60,
            ],
            'item_qty' => 2,
            'parent_qty' => 3,
            'address_data' => [
                'subtotal_incl_tax' => 60,
                'base_subtotal_incl_tax' => 60,
                'weee_total_excl_tax' => 0,
                'weee_base_total_excl_tax' => 0,
            ],
        ];

        $data['price_excl_tax_weee_non_taxable_row_not_included_in_subtotal_dynamic_multiple_weee'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAlgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => false,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new DataObject(
                        [
                            'name' => 'Recycling Fee',
                            'amount' => 10,
                        ]
                    ),
                    new DataObject(
                        [
                            'name' => 'FPT Fee',
                            'amount' => 5,
                        ]
                    ),
                ],
            ],
            'tax_rates' => [
                'store_tax_rate' => 8.25,
                'customer_tax_rate' => 8.25,
            ],
            'item' => [
                'weee_tax_applied_amount' => 15,
                'base_weee_tax_applied_amount' => 15,
                'weee_tax_applied_row_amount' => 30,
                'base_weee_tax_applied_row_amnt' => 30,
                'weee_tax_applied_amount_incl_tax' => 15,
                'base_weee_tax_applied_amount_incl_tax' => 15,
                'weee_tax_applied_row_amount_incl_tax' => 30,
                'base_weee_tax_applied_row_amnt_incl_tax' => 30,
            ],
            'item_qty' => 2,
            'item_is_parent' => true,
            'address_data' => [
                'subtotal_incl_tax' => 30,
                'base_subtotal_incl_tax' => 30,
                'weee_total_excl_tax' => 30,
                'weee_base_total_excl_tax' => 30,
            ],
            'assertSetApplied' => true,
        ];

        return $data;
    }
}
