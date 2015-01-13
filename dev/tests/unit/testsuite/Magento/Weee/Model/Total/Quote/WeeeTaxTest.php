<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Total\Quote;

use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector as CTC;

class WeeeTaxTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Constants for array keys
     */
    const KEY_WEEE_TOTALS = 'weee_total_excl_tax';
    const KEY_WEEE_BASE_TOTALS = 'weee_base_total_excl_tax';
    /**#@-*/

    /**
     * Setup tax helper with an array of methodName, returnValue
     *
     * @param array $taxConfig
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Helper\Data
     */
    protected function setupTaxHelper($taxConfig)
    {
        $taxHelper = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);

        foreach ($taxConfig as $method => $value) {
            $taxHelper->expects($this->any())->method($method)->will($this->returnValue($value));
        }

        return $taxHelper;
    }

    /**
     * Setup calculator to return tax rates
     *
     * @param array $taxRates
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\Calculation
     */
    protected function setupTaxCalculation($taxRates)
    {
        $storeTaxRate = $taxRates['store_tax_rate'];
        $customerTaxRate = $taxRates['customer_tax_rate'];

        $taxCalculation = $this->getMock('Magento\Tax\Model\Calculation', [], [], '', false);

        $rateRequest = new \Magento\Framework\Object();
        $defaultRateRequest = new \Magento\Framework\Object();

        $taxCalculation->expects($this->any())->method('getRateRequest')->will($this->returnValue($rateRequest));
        $taxCalculation
            ->expects($this->any())
            ->method('getRateOriginRequest')
            ->will($this->returnValue($defaultRateRequest));

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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Weee\Helper\Data
     */
    protected function setupWeeeHelper($weeeConfig)
    {
        $weeeHelper = $this->getMock('Magento\Weee\Helper\Data', [], [], '', false);

        foreach ($weeeConfig as $method => $value) {
            $weeeHelper->expects($this->any())->method($method)->will($this->returnValue($value));
        }

        return $weeeHelper;
    }

    /**
     * Setup an item mock
     *
     * @param float $itemQty
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item
     */
    protected function setupItemMock($itemQty)
    {
        $itemMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            [
                'getProduct',
                'getQuote',
                'getAddress',
                'getTotalQty',
                '__wakeup',
            ],
            [],
            '',
            false
        );

        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $itemMock->expects($this->any())->method('getProduct')->will($this->returnValue($productMock));
        $itemMock->expects($this->any())->method('getTotalQty')->will($this->returnValue($itemQty));

        return $itemMock;
    }

    /**
     * Setup address mock
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item $itemMock
     * @param boolean $isWeeeTaxable
     * @param array   $itemData
     * @param array   $addressData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function setupAddressMock($itemMock, $isWeeeTaxable, $itemData, $addressData)
    {
        $addressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            [
                '__wakeup',
                'getAllItems',
                'getQuote',
                'getWeeeCodeToItemMap',
                'getExtraTaxableDetails',
                'getWeeeTotalExclTax',
                'getWeeeBaseTotalExclTax',
            ],
            [],
            '',
            false
        );

        $map = [];
        $extraDetails = [];
        $weeeTotals = 0;
        $weeeBaseTotals = 0;

        if ($isWeeeTaxable) {
            $code = 'weee1-myWeeeCode';
            $map = [$code => $itemMock];
            $extraDetails = [
                'weee' => [
                    'sequence-1' => [
                        [
                            CTC::KEY_TAX_DETAILS_TYPE => 'weee',
                            CTC::KEY_TAX_DETAILS_CODE => $code,
                            CTC::KEY_TAX_DETAILS_PRICE_EXCL_TAX => $itemData['weee_tax_applied_amount'],
                            CTC::KEY_TAX_DETAILS_BASE_PRICE_EXCL_TAX => $itemData['base_weee_tax_applied_amount'],
                            CTC::KEY_TAX_DETAILS_PRICE_INCL_TAX => $itemData['weee_tax_applied_amount_incl_tax'],
                            CTC::KEY_TAX_DETAILS_BASE_PRICE_INCL_TAX =>
                                $itemData['base_weee_tax_applied_amount_incl_tax'],
                            CTC::KEY_TAX_DETAILS_ROW_TOTAL => $itemData['weee_tax_applied_row_amount'],
                            CTC::KEY_TAX_DETAILS_BASE_ROW_TOTAL => $itemData['base_weee_tax_applied_row_amnt'],
                            CTC::KEY_TAX_DETAILS_ROW_TOTAL_INCL_TAX =>
                                $itemData['weee_tax_applied_row_amount_incl_tax'],
                            CTC::KEY_TAX_DETAILS_BASE_ROW_TOTAL_INCL_TAX =>
                                $itemData['base_weee_tax_applied_row_amnt_incl_tax'],
                        ],
                    ],
                ],
            ];
        } else {
            if (isset($addressData[self::KEY_WEEE_TOTALS])) {
                $weeeTotals = $addressData[self::KEY_WEEE_TOTALS];
            }
            if (isset($addressData[self::KEY_WEEE_BASE_TOTALS])) {
                $weeeBaseTotals = $addressData[self::KEY_WEEE_BASE_TOTALS];
            }
        }

        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $storeMock = $this->getMock('Magento\Store\Model\Store', ['__wakeup', 'convertPrice'], [], '', false);
        $storeMock->expects($this->any())->method('convertPrice')->will($this->returnArgument(0));
        $quoteMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $addressMock->expects($this->any())->method('getAllItems')->will($this->returnValue([$itemMock]));
        $addressMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $addressMock->expects($this->any())->method('getWeeeCodeToItemMap')->will($this->returnValue($map));
        $addressMock->expects($this->any())->method('getExtraTaxableDetails')->will($this->returnValue($extraDetails));
        $addressMock
            ->expects($this->any())
            ->method('getWeeeTotalExclTax')
            ->will($this->returnValue($weeeTotals));
        $addressMock
            ->expects($this->any())
            ->method('getWeeeBaseTotalExclTax')
            ->will($this->returnValue($weeeBaseTotals));

        return $addressMock;
    }

    /**
     * Verify that correct fields of item has been set
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item $item
     * @param array $itemData
     */
    public function verifyItem(\Magento\Sales\Model\Quote\Item $item, $itemData)
    {
        foreach ($itemData as $key => $value) {
            $this->assertEquals($value, $item->getData($key), 'item ' . $key . ' is incorrect');
        }
    }

    /**
     * Verify that correct fields of address has been set
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Address $address
     * @param array $addressData
     */
    public function verifyAddress(\Magento\Sales\Model\Quote\Address $address, $addressData)
    {
        foreach ($addressData as $key => $value) {
            if ($key != self::KEY_WEEE_TOTALS && $key != self::KEY_WEEE_BASE_TOTALS) {
                // just check the output values
                $this->assertEquals($value, $address->getData($key), 'address ' . $key . ' is incorrect');
            }
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
     * @param array $addressData
     * @dataProvider collectDataProvider
     */
    public function testCollect($taxConfig, $weeeConfig, $taxRates, $itemData, $itemQty, $addressData = [])
    {
        $itemMock = $this->setupItemMock($itemQty);
        $addressMock = $this->setupAddressMock($itemMock, $weeeConfig['isTaxable'], $itemData, $addressData);

        $taxHelper = $this->setupTaxHelper($taxConfig);
        $weeeHelper = $this->setupWeeeHelper($weeeConfig);
        $calculator = $this->setupTaxCalculation($taxRates);

        $arguments = [
            'taxData' => $taxHelper,
            'calculation' => $calculator,
            'weeeData' => $weeeHelper,
        ];

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->weeeCollector = $helper->getObject('Magento\Weee\Model\Total\Quote\WeeeTax', $arguments);

        $this->weeeCollector->collect($addressMock);

        $this->verifyItem($itemMock, $itemData);
        $this->verifyAddress($addressMock, $addressData);
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
        // 1. When the Weee is not taxable, this collector does not change the item, but it will update the address
        //    data based on the weee totals accumulated in the previous 'weee' collector
        // 2. If the Weee amount is included in the subtotal, then it is not included in the 'weee_amount' field

        $data = [];

        $data['price_incl_tax_weee_taxable_unit_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
                'weee_tax_applied_amount' => 9.24,
                'base_weee_tax_applied_amount' => 9.24,
                'weee_tax_applied_row_amount' => 18.48,
                'base_weee_tax_applied_row_amnt' => 18.48,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'address_data' => [
                'subtotal' => 18.48,
                'base_subtotal' => 18.48,
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_amount' => 0,
                'base_weee_amount' => 0,
            ],
        ];

        $data['price_incl_tax_weee_taxable_unit_not_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => false,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
                'weee_tax_applied_amount' => 9.24,
                'base_weee_tax_applied_amount' => 9.24,
                'weee_tax_applied_row_amount' => 18.48,
                'base_weee_tax_applied_row_amnt' => 18.48,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'address_data' => [
                'subtotal' => 0,
                'base_subtotal' => 0,
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_amount' => 18.48,
                'base_weee_amount' => 18.48,
            ],
        ];

        $data['price_excl_tax_weee_taxable_unit_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
                'weee_tax_applied_amount_incl_tax' => 10.83,
                'base_weee_tax_applied_amount_incl_tax' => 10.83,
                'weee_tax_applied_row_amount_incl_tax' => 21.66,
                'base_weee_tax_applied_row_amnt_incl_tax' => 21.66,
            ],
            'item_qty' => 2,
            'address_data' => [
                'subtotal' => 20,
                'base_subtotal' => 20,
                'subtotal_incl_tax' => 21.66,
                'base_subtotal_incl_tax' => 21.66,
                'weee_amount' => 0,
                'base_weee_amount' => 0,
            ],
        ];

        $data['price_incl_tax_weee_non_taxable_unit_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
            ],
            'item_qty' => 2,
            'address_data' => [
                self::KEY_WEEE_TOTALS => 20,
                self::KEY_WEEE_BASE_TOTALS => 20,
                'subtotal' => 20,
                'base_subtotal' => 20,
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_amount' => 0,
                'base_weee_amount' => 0,
            ],
        ];

        $data['price_excl_tax_weee_non_taxable_unit_include_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAgorithm' => Calculation::CALC_UNIT_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
            ],
            'item_qty' => 2,
            'address_data' => [
                self::KEY_WEEE_TOTALS => 20,
                self::KEY_WEEE_BASE_TOTALS => 20,
                'subtotal' => 20,
                'base_subtotal' => 20,
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_amount' => 0,
                'base_weee_amount' => 0,
            ],
        ];

        $data['price_incl_tax_weee_taxable_row_include_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
                'weee_tax_applied_amount' => 9.24,
                'base_weee_tax_applied_amount' => 9.24,
                'weee_tax_applied_row_amount' => 18.48,
                'base_weee_tax_applied_row_amnt' => 18.48,
                'weee_tax_applied_amount_incl_tax' => 10,
                'base_weee_tax_applied_amount_incl_tax' => 10,
                'weee_tax_applied_row_amount_incl_tax' => 20,
                'base_weee_tax_applied_row_amnt_incl_tax' => 20,
            ],
            'item_qty' => 2,
            'address_data' => [
                'subtotal' => 18.48,
                'base_subtotal' => 18.48,
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_amount' => 0,
                'base_weee_amount' => 0,
            ],
        ];

        $data['price_excl_tax_weee_taxable_row_include_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => true,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
                'weee_tax_applied_amount_incl_tax' => 10.83,
                'base_weee_tax_applied_amount_incl_tax' => 10.83,
                'weee_tax_applied_row_amount_incl_tax' => 21.65,
                'base_weee_tax_applied_row_amnt_incl_tax' => 21.65,
            ],
            'item_qty' => 2,
            'address_data' => [
                'subtotal' => 20,
                'base_subtotal' => 20,
                'subtotal_incl_tax' => 21.65,
                'base_subtotal_incl_tax' => 21.65,
                'weee_amount' => 0,
                'base_weee_amount' => 0,
            ],
        ];

        $data['price_incl_tax_weee_non_taxable_row_include_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => true,
                'getCalculationAgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => true,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
            ],
            'item_qty' => 2,
            'address_data' => [
                self::KEY_WEEE_TOTALS => 20,
                self::KEY_WEEE_BASE_TOTALS => 20,
                'subtotal' => 20,
                'base_subtotal' => 20,
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_amount' => 0,
                'base_weee_amount' => 0,
            ],
        ];

        $data['price_excl_tax_weee_non_taxable_row_not_included_in_subtotal'] = [
            'tax_config' => [
                'priceIncludesTax' => false,
                'getCalculationAgorithm' => Calculation::CALC_ROW_BASE,
            ],
            'weee_config' => [
                'isEnabled' => true,
                'includeInSubtotal' => false,
                'isTaxable' => false,
                'getApplied' => [],
                'getProductWeeeAttributes' => [
                    new \Magento\Framework\Object(
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
            ],
            'item_qty' => 2,
            'address_data' => [
                self::KEY_WEEE_TOTALS => 20,
                self::KEY_WEEE_BASE_TOTALS => 20,
                'subtotal' => 0,
                'base_subtotal' => 0,
                'subtotal_incl_tax' => 20,
                'base_subtotal_incl_tax' => 20,
                'weee_amount' => 20,
                'base_weee_amount' => 20,
            ],
        ];

        return $data;
    }
}
