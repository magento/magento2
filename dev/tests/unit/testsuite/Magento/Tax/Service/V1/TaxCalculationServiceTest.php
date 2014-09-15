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

namespace Magento\Tax\Service\V1;

use Magento\Tax\Service\V1\Data\QuoteDetails\Item as QuoteDetailsItem;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Test TaxCalculationService
 */
class TaxCalculationServiceTest extends \PHPUnit_Framework_TestCase
{
    const TAX = 0.1;

    /** @var \Magento\Tax\Service\V1\TaxCalculationService */
    private $taxCalculationService;

    /** @var \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder */
    private $quoteDetailsBuilder;

    /** @var  \Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder*/
    private $taxDetailsItemBuilder;

    /** @var \Magento\Tax\Service\V1\Data\TaxDetailsBuilder */
    private $taxDetailsBuilder;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\StoreManagerInterface */
    private $storeManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\Calculation\CalculatorFactory */
    private $calculatorFactory;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->quoteDetailsBuilder = $objectManager->getObject('Magento\Tax\Service\V1\Data\QuoteDetailsBuilder');
        $this->storeManager = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()->getMock();
        $this->calculatorFactory = $this->getMockBuilder('Magento\Tax\Model\Calculation\CalculatorFactory')
            ->disableOriginalConstructor()->getMock();
        $calculationTool = $this->getMockBuilder('Magento\Tax\Model\Calculation')
            ->disableOriginalConstructor()->getMock();
        $calculationTool->expects($this->any())
            ->method('round')
            ->will($this->returnArgument(0));
        $this->taxDetailsBuilder = $objectManager->getObject('Magento\Tax\Service\V1\Data\TaxDetailsBuilder');
        $this->taxDetailsItemBuilder = $objectManager->getObject('Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder');
        $this->taxCalculationService = $objectManager->getObject(
            'Magento\Tax\Service\V1\TaxCalculationService',
            [
                'calculation' => $calculationTool,
                'calculatorFactory' => $this->calculatorFactory,
                'taxDetailsBuilder' => $this->taxDetailsBuilder,
                'taxDetailsItemBuilder' => $this->taxDetailsItemBuilder,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * @param array $quoteDetailsData
     * @param array $taxDetailsData
     * @param string $calculateCallback Name of a function within this test class which will be executed to create
     *      a tax details item.
     * @return void
     * @dataProvider calculateTaxProvider
     */
    public function testCalculateTax($quoteDetailsData, $taxDetailsData, $calculateCallback = 'createTaxDetailsItem')
    {
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $calculatorMock = $this->getMockBuilder('\Magento\Tax\Model\Calculation\AbstractCalculator')
            ->disableOriginalConstructor()->setMethods(['calculate'])->getMockForAbstractClass();
        $this->calculatorFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($calculatorMock));
        $calculatorMock->expects($this->any())
            ->method('calculate')
            ->will($this->returnCallback([$this, $calculateCallback]));

        $quoteDetails = $this->quoteDetailsBuilder->populateWithArray($quoteDetailsData)->create();

        $taxDetails = $this->taxCalculationService->calculateTax($quoteDetails);

        $this->assertEquals($taxDetailsData, $taxDetails->__toArray());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculateTaxProvider()
    {
        return [
            'empty' => [
                'quoteDetails' => [],
                'taxDetails' => [
                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'discount_tax_compensation_amount' => 0,
                    'applied_taxes' => [],
                    'items' => [],
                ],
            ],
            'one_item' => [
                'quoteDetails' => [
                    'billing_address' => [
                        'postcode' => '55555',
                        'country_id' => 'US',
                        'region' => ['region_id' => 42],
                    ],
                    'shipping_address' => [
                        'postcode' => '55555',
                        'country_id' => 'US',
                        'region' => ['region_id' => 42],
                    ],
                    'customer_tax_class_id' => 'DefaultCustomerClass',
                    'items' => [
                        [
                            'code' => 'sku_1',
                            'type' => 'product',
                            'quantity' => 10,
                            'unit_price' => 1,
                            'tax_class_id' => 'DefaultProductClass',
                        ]
                    ],
                ],
                'taxDetails' => [
                    'subtotal' => 10,
                    'tax_amount' => 1,
                    'discount_tax_compensation_amount' => 0,
                    'applied_taxes' => [],
                    'items' => [
                        'sku_1' => [
                            'code' => 'sku_1',
                            'type' => 'product',
                            'price' => 1,
                            'row_total' => 10,
                            'tax_percent' => self::TAX,
                            'row_tax' => 1,
                            'row_total_incl_tax' => 11,
                            'price_incl_tax' => 1.1,
                        ]
                    ],
                ],
            ], // End one_item
            'empty_applied' => [
                'quoteDetails' => [],
                'taxDetails' => [
                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'discount_tax_compensation_amount' => 0,
                    'applied_taxes' => [],
                    'items' => [],
                ],
                'calculateCallbacks' => 'createTaxDetailsItemWithAppliedTaxes',
            ], // End empty_applied
            'one_item_applied' => [
                'quoteDetails' => [
                    'billing_address' => [
                        'postcode' => '55555',
                        'country_id' => 'US',
                        'region' => ['region_id' => 42],
                    ],
                    'shipping_address' => [
                        'postcode' => '55555',
                        'country_id' => 'US',
                        'region' => ['region_id' => 42],
                    ],
                    'customer_tax_class_id' => 'DefaultCustomerClass',
                    'items' => [
                        [
                            'code' => 'sku_1',
                            'type' => 'product',
                            'quantity' => 10,
                            'unit_price' => 1,
                            'tax_class_id' => 'DefaultProductClass',
                        ]
                    ],
                ],
                'taxDetails' => [
                    'subtotal' => 10,
                    'tax_amount' => 1,
                    'discount_tax_compensation_amount' => 0,
                    'applied_taxes' => [
                        [
                            'amount' => 0.1,
                            'percent' => self::TAX,
                            'rates' => [
                                [
                                    'code' => 'TAX',
                                    'title' => 'Tax',
                                    'percent' => self::TAX,
                                ]
                            ],
                            'tax_rate_key' => 'TAX_RATE',
                        ]
                    ],
                    'items' => [
                        'sku_1' => [
                            'code' => 'sku_1',
                            'type' => 'product',
                            'price' => 1,
                            'row_total' => 10,
                            'tax_percent' => self::TAX,
                            'row_tax' => 1,
                            'row_total_incl_tax' => 11,
                            'price_incl_tax' => 1.1,
                            'applied_taxes' => [
                                [
                                    'amount' => 0.1,
                                    'percent' => self::TAX,
                                    'rates' => [
                                        [
                                            'code' => 'TAX',
                                            'title' => 'Tax',
                                            'percent' => self::TAX,
                                        ]
                                    ],
                                    'tax_rate_key' => 'TAX_RATE',
                                ]
                            ],
                        ]
                    ],
                ],
                'calculateCallbacks' => 'createTaxDetailsItemWithAppliedTaxes',
            ], // End one_item_applied
            'bundled_items_applied' => [
                'quoteDetails' => [
                    'billing_address' => [
                        'postcode' => '55555',
                        'country_id' => 'US',
                        'region' => ['region_id' => 42],
                    ],
                    'shipping_address' => [
                        'postcode' => '55555',
                        'country_id' => 'US',
                        'region' => ['region_id' => 42],
                    ],
                    'customer_tax_class_id' => 'DefaultCustomerClass',
                    'items' => [
                        [
                            'code' => 'sku_1',
                            'type' => 'product',
                            'quantity' => 10,
                            'unit_price' => 1,
                            'tax_class_id' => 'DefaultProductClass',
                            'parent_code' => 'bundle',
                        ],
                        [
                            'code' => 'sku_2',
                            'type' => 'product',
                            'quantity' => 1,
                            'unit_price' => 10,
                            'tax_class_id' => 'DefaultProductClass',
                            'parent_code' => 'bundle',
                        ],
                        [
                            'code' => 'bundle',
                            'type' => 'product',
                            'quantity' => 2,
                            'unit_price' => 0,
                            'tax_class_id' => 'DefaultProductClass',
                        ],
                    ],
                ],
                'taxDetails' => [
                    'subtotal' => 20,
                    'tax_amount' => 2,
                    'discount_tax_compensation_amount' => 0,
                    'applied_taxes' => [
                        [
                            'amount' => 1.1,
                            'percent' => self::TAX,
                            'rates' => [
                                [
                                    'code' => 'TAX',
                                    'title' => 'Tax',
                                    'percent' => self::TAX,
                                ]
                            ],
                            'tax_rate_key' => 'TAX_RATE',
                        ]
                    ],
                    'items' => [
                        'sku_1' => [
                            'code' => 'sku_1',
                            'type' => 'product',
                            'price' => 1,
                            'row_total' => 10,
                            'tax_percent' => self::TAX,
                            'row_tax' => 1,
                            'row_total_incl_tax' => 11,
                            'price_incl_tax' => 1.1,
                            'applied_taxes' => [
                                [
                                    'amount' => 0.1,
                                    'percent' => self::TAX,
                                    'rates' => [
                                        [
                                            'code' => 'TAX',
                                            'title' => 'Tax',
                                            'percent' => self::TAX,
                                        ]
                                    ],
                                    'tax_rate_key' => 'TAX_RATE',
                                ]
                            ],
                        ],
                        'sku_2' => [
                            'code' => 'sku_2',
                            'type' => 'product',
                            'price' => 10,
                            'row_total' => 10,
                            'tax_percent' => self::TAX,
                            'row_tax' => 1,
                            'row_total_incl_tax' => 11,
                            'price_incl_tax' => 11,
                            'applied_taxes' => [
                                [
                                    'amount' => 1,
                                    'percent' => self::TAX,
                                    'rates' => [
                                        [
                                            'code' => 'TAX',
                                            'title' => 'Tax',
                                            'percent' => self::TAX,
                                        ]
                                    ],
                                    'tax_rate_key' => 'TAX_RATE',
                                ]
                            ],
                        ],
                        'bundle' => [
                            'price' => 10,
                            'price_incl_tax' => 11,
                            'row_total' => 20,
                            'row_total_incl_tax' => 22,
                            'row_tax' => 2,
                            'code' => 'bundle',
                            'type' => 'product',
                        ],
                    ],
                ],
                'calculateCallbacks' => 'createTaxDetailsItemWithAppliedTaxes',
            ], // End bundled_items_applied
        ];
    }

    /**
     * Callback function that creates a tax details item from a quote details item for testing.
     *
     * @param QuoteDetailsItem $item
     * @return Data\TaxDetails\Item
     */
    public function createTaxDetailsItem(QuoteDetailsItem $item)
    {
        $rowTotal = $item->getUnitPrice() * $item->getQuantity();
        $rowTax = $rowTotal * self::TAX;
        return $this->taxDetailsItemBuilder->populateWithArray($item->__toArray())
            ->setPrice($item->getUnitPrice())
            ->setRowTotal($rowTotal)
            ->setTaxPercent(self::TAX)
            ->setRowTax($rowTax)
            ->setRowTotalInclTax($rowTotal + $rowTax)
            ->setPriceInclTax($item->getUnitPrice() + ($item->getUnitPrice() * self::TAX))
            ->create();
    }

    /**
     * Callback function that creates a tax details item with applied taxes from a quote details item for testing.
     *
     * @param QuoteDetailsItem $item
     * @return Data\TaxDetails\Item
     */
    public function createTaxDetailsItemWithAppliedTaxes(QuoteDetailsItem $item)
    {
        $appliedTaxRateBuilder = $this->taxDetailsBuilder->getAppliedTaxBuilder();
        $taxRateBuilder = $appliedTaxRateBuilder->getAppliedTaxRateBuilder();
        $rate = $taxRateBuilder
            ->setPercent(self::TAX)
            ->setCode('TAX')
            ->setTitle('Tax')
            ->create();
        $appliedTaxes = $appliedTaxRateBuilder
            ->setAmount($item->getUnitPrice() * self::TAX)
            ->setTaxRateKey('TAX_RATE')
            ->setPercent(self::TAX)
            ->setRates([$rate])
            ->create();
        return $this->taxDetailsItemBuilder->populate($this->createTaxDetailsItem($item))
            ->setAppliedTaxes([$appliedTaxes])
            ->create();
    }
}
