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

use Magento\Tax\Model\ClassModel;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 */
class TaxCalculationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * Tax calculation service
     *
     * @var \Magento\Tax\Service\V1\TaxCalculationService
     */
    private $taxCalculationService;

    /**
     * Tax Details Builder
     *
     * @var \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder
     */
    private $quoteDetailsBuilder;

    /**
     * Tax Details Item Builder
     *
     * @var \Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder
     */
    private $quoteDetailsItemBuilder;

    /**
     * Array of default tax classes ids
     *
     * Key is class name
     *
     * @var int[]
     */
    private $taxClasses;

    /**
     * Array of default tax rates ids.
     *
     * Key is rate percentage as string.
     *
     * @var int[]
     */
    private $taxRates;

    /**
     * Array of default tax rules ids.
     *
     * Key is rule code.
     *
     * @var int[]
     */
    private $taxRules;

    /**
     * Helps in creating required tax rules.
     *
     * @var TaxRuleFixtureFactory
     */
    private $taxRuleFixtureFactory;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteDetailsBuilder = $this->objectManager
            ->create('Magento\Tax\Service\V1\Data\QuoteDetailsBuilder');
        $this->quoteDetailsItemBuilder = $this->objectManager
            ->create('Magento\Tax\Service\V1\data\QuoteDetails\ItemBuilder');
        $this->taxCalculationService = $this->objectManager->get('\Magento\Tax\Service\V1\TaxCalculationService');
        $this->taxRuleFixtureFactory = new TaxRuleFixtureFactory();

        $this->setUpDefaultRules();
    }

    protected function tearDown()
    {
        $this->tearDownDefaultRules();
    }

    /**
     * @magentoConfigFixture current_store tax/calculation/algorithm UNIT_BASE_CALCULATION
     * @dataProvider calculateUnitBasedDataProvider
     */
    public function testCalculateTaxUnitBased($quoteDetailsData, $expected)
    {
        $quoteDetailsData = $this->performTaxClassSubstitution($quoteDetailsData);
        $quoteDetails = $this->quoteDetailsBuilder->populateWithArray($quoteDetailsData)->create();

        $taxDetails = $this->taxCalculationService->calculateTax($quoteDetails, 1);
        $this->assertEquals($expected, $taxDetails->__toArray());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculateUnitBasedDataProvider()
    {
        $baseQuote = $this->getBaseQuoteData();
        $oneProduct = $baseQuote;
        $oneProduct['items'][] = [
            'code' => 'sku_1',
            'type' => 'product',
            'quantity' => 2,
            'unit_price' => 10,
            'tax_class_id' => 'DefaultProductClass',
        ];
        $oneProductResults = [
            'subtotal' => 20,
            'tax_amount' => 1.5,
            'discount_amount' => 0,
            'items' => [
                [
                    'row_tax' => 1.5,
                    'price' => 10,
                    'price_incl_tax' => 10.75,
                    'row_total' => 20,
                    'taxable_amount' => 10,
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                ],
            ],
        ];

        $oneProductInclTax = $baseQuote;
        $oneProductInclTax['items'][] = [
            'code' => 'sku_1',
            'type' => 'product',
            'quantity' => 2,
            'unit_price' => 10.75,
            'row_total' => 21.5,
            'tax_class_id' => 'DefaultProductClass',
            'tax_included' => true,
        ];
        $oneProductInclTaxResults = $oneProductResults;
        // TODO: I think this is a bug, but the old code behaved this way so keeping it for now.
        $oneProductInclTaxResults['items'][0]['taxable_amount'] = 10.75;

        $oneProductInclTaxDiffRate = $baseQuote;
        $oneProductInclTaxDiffRate['items'][] = [
            'code' => 'sku_1',
            'type' => 'product',
            'quantity' => 2,
            'unit_price' => 11,
            'row_total' => 22,
            'tax_class_id' => 'HigherProductClass',
            'tax_included' => true,
        ];
        $oneProductInclTaxDiffRateResults = [
            'subtotal' => 20,
            'tax_amount' => 4.4,
            'discount_amount' => 0,
            'items' => [
                [
                    'price' => 10,
                    'price_incl_tax' => 12.2,
                    'row_total' => 20,
                    'taxable_amount' => 12.2, // TODO: Possible bug, shouldn't this be 10?
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 22.0,
                    'row_tax' => 4.4,
                ],
            ],
        ];

        $twoProducts = $baseQuote;
        $twoProducts['items'] = [
            [
                'code' => 'sku_1',
                'type' => 'product',
                'quantity' => 2,
                'unit_price' => 10,
                'row_total' => 20,
                'tax_class_id' => 'DefaultProductClass',
            ],
            [
                'code' => 'sku_2',
                'type' => 'product',
                'quantity' => 20,
                'unit_price' => 11,
                'row_total' => 220,
                'tax_class_id' => 'DefaultProductClass',
            ]
        ];
        $twoProductsResults = [
            'subtotal' => 240,
            'tax_amount' => 18.1,
            'discount_amount' => 0,
            'items' => [
                [
                    'price' => 10,
                    'price_incl_tax' => 10.75,
                    'row_total' => 20,
                    'taxable_amount' => 10,
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => 1.5,
                ],
                [
                    'price' => 11,
                    'price_incl_tax' => 11.83,
                    'row_total' => 220,
                    'taxable_amount' => 11,
                    'code' => 'sku_2',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => 16.6,
                ],
            ],
        ];

        $twoProductsInclTax = $baseQuote;
        $twoProductsInclTax['items'] = [
            [
                'code' => 'sku_1',
                'type' => 'product',
                'quantity' => 2,
                'unit_price' => 10.75,
                'row_total' => 21.5,
                'tax_class_id' => 'DefaultProductClass',
                'tax_included' => true,
            ],
            [
                'code' => 'sku_2',
                'type' => 'product',
                'quantity' => 20,
                'unit_price' => 11.83,
                'row_total' => 236.6,
                'tax_class_id' => 'DefaultProductClass',
                'tax_included' => true,
            ]
        ];
        $twoProductInclTaxResults = $twoProductsResults;
        // TODO: I think this is a bug, but the old code behaved this way so keeping it for now.
        $twoProductInclTaxResults['items'][0]['taxable_amount'] = 10.75;
        $twoProductInclTaxResults['items'][1]['taxable_amount'] = 11.83;

        $bundleProduct = $baseQuote;
        $bundleProduct['items'][] = [
            'code' => 'sku_1',
            'type' => 'product',
            'quantity' => 1,
            'unit_price' => 10,
            'row_total' => 10,
            'tax_class_id' => 'DefaultProductClass',
            'parent_code' => 'bundle',
        ];
        $bundleProduct['items'][] = [
            'code' => 'bundle',
            'type' => 'product',
            'quantity' => 2,
            'unit_price' => 0,
            'row_total' => 0,
            'tax_class_id' => 'DefaultProductClass',
        ];
        $bundleProductResults = [
            'subtotal' => 20,
            'tax_amount' => 1.5,
            'discount_amount' => 0,
            'items' => [
                [
                    'row_tax' => 1.5,
                    'price' => 10,
                    'price_incl_tax' => 10.75,
                    'row_total' => 20,
                    'taxable_amount' => 10,
                    'code' => 'bundle',
                    'type' => 'product',
                ],
            ],
        ];

        return [
            'one product' => [
                'quote_details' => $oneProduct,
                'expected_tax_details' => $oneProductResults,
            ],
            'one product, tax included' => [
                'quote_details' => $oneProductInclTax,
                'expected_tax_details' => $oneProductInclTaxResults,
            ],
            'one product, tax included but differs from store rate' => [
                'quote_details' => $oneProductInclTaxDiffRate,
                'expected_tax_details' => $oneProductInclTaxDiffRateResults,
            ],
            'two products' => [
                'quote_details' => $twoProducts,
                'expected_tax_details' => $twoProductsResults,
            ],
            'two products, tax included' => [
                'quote_details' => $twoProductsInclTax,
                'expected_tax_details' => $twoProductInclTaxResults,
            ],
            'bundle product' => [
                'quote_details' => $bundleProduct,
                'expected_tax_details' => $bundleProductResults,
            ],
        ];
    }

    /**
     * @dataProvider calculateTaxTotalBasedDataProvider
     * @magentoConfigFixture current_store tax/calculation/algorithm TOTAL_BASE_CALCULATION
     */
    public function testCalculateTaxTotalBased($quoteDetailsData, $expectedTaxDetails, $storeId = null)
    {
        $quoteDetailsData = $this->performTaxClassSubstitution($quoteDetailsData);

        $quoteDetails = $this->quoteDetailsBuilder->populateWithArray($quoteDetailsData)->create();

        $taxDetails = $this->taxCalculationService->calculateTax($quoteDetails, $storeId);

        $this->assertEquals($expectedTaxDetails, $taxDetails->__toArray());
    }

    public function calculateTaxTotalBasedDataProvider()
    {
        return array_merge(
            $this->calculateTaxNoTaxInclDataProvider(),
            $this->calculateTaxTaxInclDataProvider(),
            $this->calculateTaxRoundingDataProvider()
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculateTaxNoTaxInclDataProvider()
    {
        $prodNoTaxInclBase = [
            'quote_details' => [
                'shipping_address' => [
                    'postcode' => '55555',
                    'country_id' => 'US',
                    'region' => ['region_id' => 42],
                ],
                'items' => [
                    [
                        'code' => 'code',
                        'type' => 'type',
                        'quantity' => 1,
                        'unit_price' => 10.0,
                        'row_total' => 10.0,
                        'tax_included' => false,
                    ],
                ],
                'customer_tax_class_id' => 'DefaultCustomerClass'
            ],
            'expected_tax_details' => [
                'subtotal' => 10.0,
                'tax_amount' => 0.0,
                'discount_amount' => 0.0,
                'items' => [],
            ],
            'store_id' => null,
        ];

        $prodQuoteDetailItemBase = [
            'code' => 'code',
            'type' => 'type',
            'quantity' => 1,
            'unit_price' => 10.0,
            'row_total' => 10.0,
            'tax_included' => false,
        ];

        $quoteDetailItemWithDefaultProductTaxClass = $prodQuoteDetailItemBase;
        $quoteDetailItemWithDefaultProductTaxClass['tax_class_id'] = 'DefaultProductClass';

        $prodExpectedItemWithNoProductTaxClass = [
            'row_tax' => 0,
            'price' => 10.0,
            'price_incl_tax' => 10.0,
            'row_total' => 10.0,
            'taxable_amount' => 10.0,
            'code' => 'code',
            'type' => 'type',
            'tax_percent' => 0,
        ];

        $prodExpectedItemWithDefaultProductTaxClass = [
            'row_tax' => 0.75,
            'price' => 10.0,
            'price_incl_tax' => 10.75,
            'row_total' => 10.0,
            'taxable_amount' => 10.0,
            'code' => 'code',
            'type' => 'type',
            'tax_percent' => 7.5,
        ];

        $prodWithStoreIdWithTaxClassId = $prodNoTaxInclBase;
        $prodWithStoreIdWithoutTaxClassId = $prodNoTaxInclBase;
        $prodWithoutStoreIdWithTaxClassId = $prodNoTaxInclBase;
        $prodWithoutStoreIdWithoutTaxClassId = $prodNoTaxInclBase;

        $prodWithStoreIdWithTaxClassId['store_id'] = 1;
        $prodWithStoreIdWithTaxClassId['quote_details']['items'][] = $quoteDetailItemWithDefaultProductTaxClass;
        $prodWithStoreIdWithTaxClassId['expected_tax_details']['tax_amount'] = 0.75;
        $prodWithStoreIdWithTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithDefaultProductTaxClass;

        $prodWithStoreIdWithoutTaxClassId['store_id'] = 1;
        $prodWithStoreIdWithoutTaxClassId['quote_details']['items'][] = $prodQuoteDetailItemBase;
        $prodWithStoreIdWithoutTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithNoProductTaxClass;

        $prodWithoutStoreIdWithTaxClassId['quote_details']['items'][] =
            $quoteDetailItemWithDefaultProductTaxClass;
        $prodWithoutStoreIdWithTaxClassId['expected_tax_details']['tax_amount'] = 0.75;
        $prodWithoutStoreIdWithTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithDefaultProductTaxClass;

        $prodWithoutStoreIdWithoutTaxClassId['quote_details']['items'][] = $prodQuoteDetailItemBase;
        $prodWithoutStoreIdWithoutTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithNoProductTaxClass;

        return [
            'product with store id, with tax class id' => $prodWithStoreIdWithTaxClassId,
            'product with store id, without tax class id' => $prodWithStoreIdWithoutTaxClassId,
            'product without store id, with tax class id' => $prodWithoutStoreIdWithTaxClassId,
            'product without store id, without tax class id' => $prodWithoutStoreIdWithoutTaxClassId,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculateTaxTaxInclDataProvider()
    {
        $productTaxInclBase = [
            'quote_details' => [
                'shipping_address' => [
                    'postcode' => '55555',
                    'country_id' => 'US',
                    'region' => ['region_id' => 42],
                ],
                'items' => [
                    [
                        'code' => 'code',
                        'type' => 'type',
                        'quantity' => 1,
                        'unit_price' => 10.0,
                        'row_total' => 10.0,
                        'tax_included' => true,
                    ],
                ],
                'customer_tax_class_id' => 'DefaultCustomerClass'
            ],
            'expected_tax_details' => [
                'subtotal' => 10.0,
                'tax_amount' => 0.0,
                'discount_amount' => 0.0,
                'items' => [],
            ],
            'store_id' => null,
        ];

        $productTaxInclQuoteDetailItemBase = [
            'code' => 'code',
            'type' => 'type',
            'quantity' => 1,
            'unit_price' => 10.0,
            'row_total' => 10.0,
            'tax_included' => true,
        ];

        $quoteDetailTaxInclItemWithDefaultProductTaxClass = $productTaxInclQuoteDetailItemBase;
        $quoteDetailTaxInclItemWithDefaultProductTaxClass['tax_class_id'] = 'DefaultProductClass';

        $productTaxInclExpectedItemWithNoProductTaxClass = [
            'row_tax' => 0,
            'price' => 10.0,
            'price_incl_tax' => 10.0,
            'row_total' => 10.0,
            'taxable_amount' => 10.0,
            'code' => 'code',
            'type' => 'type',
            'tax_percent' => 0,
        ];

        $productTaxInclExpectedItemWithDefaultProductTaxClass = [
            'row_tax' => 0.70,
            'price' => 9.30,
            'price_incl_tax' => 10.00,
            'row_total' => 9.30,
            'taxable_amount' => 9.30,
            'code' => 'code',
            'type' => 'type',
            'tax_percent' => 7.5,
        ];

        $productInclTaxWithStoreIdWithTaxClassId = $productTaxInclBase;
        $productInclTaxWithStoreIdWithoutTaxClassId = $productTaxInclBase;
        $productInclTaxWithoutStoreIdWithTaxClassId = $productTaxInclBase;
        $productInclTaxWithoutStoreIdWithoutTaxClassId = $productTaxInclBase;

        $productInclTaxWithStoreIdWithTaxClassId['store_id'] = 1;
        $productInclTaxWithStoreIdWithTaxClassId['quote_details']['items'][] =
            $quoteDetailTaxInclItemWithDefaultProductTaxClass;
        $productInclTaxWithStoreIdWithTaxClassId['expected_tax_details']['tax_amount'] = 0.70;
        $productInclTaxWithStoreIdWithTaxClassId['expected_tax_details']['subtotal'] = 9.30;
        $productInclTaxWithStoreIdWithTaxClassId['expected_tax_details']['items'][] =
            $productTaxInclExpectedItemWithDefaultProductTaxClass;
        $productInclTaxWithStoreIdWithTaxClassId['expected_tax_details']['items'][0]['taxable_amount'] = 10.00;

        $productInclTaxWithStoreIdWithoutTaxClassId['store_id'] = 1;
        $productInclTaxWithStoreIdWithoutTaxClassId['quote_details']['items'][] =
            $productTaxInclQuoteDetailItemBase;
        $productInclTaxWithStoreIdWithoutTaxClassId['expected_tax_details']['items'][] =
            $productTaxInclExpectedItemWithNoProductTaxClass;

        $productInclTaxWithoutStoreIdWithTaxClassId['quote_details']['items'][] =
            $quoteDetailTaxInclItemWithDefaultProductTaxClass;
        $productInclTaxWithoutStoreIdWithTaxClassId['expected_tax_details']['tax_amount'] = 0.70;
        $productInclTaxWithoutStoreIdWithTaxClassId['expected_tax_details']['subtotal'] = 9.30;
        $productInclTaxWithoutStoreIdWithTaxClassId['expected_tax_details']['items'][] =
            $productTaxInclExpectedItemWithDefaultProductTaxClass;
        /* TODO: BUG? */
        $productInclTaxWithoutStoreIdWithTaxClassId['expected_tax_details']['items'][0]['taxable_amount'] = 10.00;

        $productInclTaxWithoutStoreIdWithoutTaxClassId['quote_details']['items'][] = $productTaxInclQuoteDetailItemBase;
        $productInclTaxWithoutStoreIdWithoutTaxClassId['expected_tax_details']['items'][] =
            $productTaxInclExpectedItemWithNoProductTaxClass;

        return [
            'product incl tax with store id, with tax class id' => $productInclTaxWithStoreIdWithTaxClassId,
            'product incl tax with store id, without tax class id' => $productInclTaxWithStoreIdWithoutTaxClassId,
            'product incl tax without store id, with tax class id' => $productInclTaxWithoutStoreIdWithTaxClassId,
            'product incl tax without store id, without tax class id' => $productInclTaxWithoutStoreIdWithoutTaxClassId,
        ];
    }

    public function calculateTaxRoundingDataProvider()
    {
        $prodRoundingNoTaxInclBase = [
            'quote_details' => [
                'shipping_address' => [
                    'postcode' => '55555',
                    'country_id' => 'US',
                    'region' => ['region_id' => 42],
                ],
                'items' => [
                    [
                        'code' => 'code',
                        'type' => 'type',
                        'quantity' => 2,
                        'unit_price' => 7.97,
                        'row_total' => 15.94,
                        'tax_included' => false,
                    ],
                ],
                'customer_tax_class_id' => 'DefaultCustomerClass'
            ],
            'expected_tax_details' => [
                'subtotal' => 15.94,
                'tax_amount' => 0.0,
                'discount_amount' => 0.0,
                'items' => [],
            ],
            'store_id' => null,
        ];

        $prodQuoteDetailItemBase = [
            'code' => 'code',
            'type' => 'type',
            'quantity' => 2,
            'unit_price' => 7.97,
            'row_total' => 15.94,
            'tax_included' => false,
        ];

        $quoteDetailItemWithDefaultProductTaxClass = $prodQuoteDetailItemBase;
        $quoteDetailItemWithDefaultProductTaxClass['tax_class_id'] = 'DefaultProductClass';

        $prodExpectedItemWithNoProductTaxClass = [
            'row_tax' => 0,
            'price' => 7.97,
            'price_incl_tax' => 7.97,
            'row_total' => 15.94,
            'taxable_amount' => 15.94,
            'code' => 'code',
            'type' => 'type',
            'tax_percent' => 0,
        ];

        $prodExpectedItemWithDefaultProductTaxClass = [
            'row_tax' => 1.20,
            'price' => 7.97,
            'price_incl_tax' => 8.57,
            'row_total' => 15.94,
            'taxable_amount' => 15.94,
            'code' => 'code',
            'type' => 'type',
            'tax_percent' => 7.5,
        ];

        $prodWithStoreIdWithTaxClassId = $prodRoundingNoTaxInclBase;
        $prodWithStoreIdWithoutTaxClassId = $prodRoundingNoTaxInclBase;
        $prodWithoutStoreIdWithTaxClassId = $prodRoundingNoTaxInclBase;
        $prodWithoutStoreIdWithoutTaxClassId = $prodRoundingNoTaxInclBase;

        $prodWithStoreIdWithTaxClassId['store_id'] = 1;
        $prodWithStoreIdWithTaxClassId['quote_details']['items'][] = $quoteDetailItemWithDefaultProductTaxClass;
        $prodWithStoreIdWithTaxClassId['expected_tax_details']['tax_amount'] = 1.20;
        $prodWithStoreIdWithTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithDefaultProductTaxClass;

        $prodWithStoreIdWithoutTaxClassId['store_id'] = 1;
        $prodWithStoreIdWithoutTaxClassId['quote_details']['items'][] = $prodQuoteDetailItemBase;
        $prodWithStoreIdWithoutTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithNoProductTaxClass;

        $prodWithoutStoreIdWithTaxClassId['quote_details']['items'][] =
            $quoteDetailItemWithDefaultProductTaxClass;
        $prodWithoutStoreIdWithTaxClassId['expected_tax_details']['tax_amount'] = 1.20;
        $prodWithoutStoreIdWithTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithDefaultProductTaxClass;

        $prodWithoutStoreIdWithoutTaxClassId['quote_details']['items'][] = $prodQuoteDetailItemBase;
        $prodWithoutStoreIdWithoutTaxClassId['expected_tax_details']['items'][] =
            $prodExpectedItemWithNoProductTaxClass;

        return [
            'rounding product with store id, with tax class id' => $prodWithStoreIdWithTaxClassId,
            'rounding product with store id, without tax class id' => $prodWithStoreIdWithoutTaxClassId,
            'rounding product without store id, with tax class id' => $prodWithoutStoreIdWithTaxClassId,
            'rounding product without store id, without tax class id' => $prodWithoutStoreIdWithoutTaxClassId,
        ];
    }


    /**
     * @magentoDbIsolation enabled
     * @dataProvider calculateTaxRowBasedDataProvider
     * @magentoConfigFixture default_store tax/calculation/algorithm ROW_BASE_CALCULATION
     */
    public function testCalculateTaxRowBased($quoteDetailsData, $expectedTaxDetails)
    {
        $quoteDetailsData = $this->performTaxClassSubstitution($quoteDetailsData);

        $quoteDetails = $this->quoteDetailsBuilder->populateWithArray($quoteDetailsData)->create();

        $taxDetails = $this->taxCalculationService->calculateTax($quoteDetails);

        $this->assertEquals($expectedTaxDetails, $taxDetails->__toArray());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculateTaxRowBasedDataProvider()
    {
        $baseQuote = $this->getBaseQuoteData();
        $oneProduct = $baseQuote;
        $oneProduct['items'][] = [
            'code' => 'sku_1',
            'type' => 'product',
            'quantity' => 10,
            'unit_price' => 1,
            'tax_class_id' => 'DefaultProductClass',
        ];
        $oneProductResults = [
            'subtotal' => 10,
            'tax_amount' => 0.75,
            'discount_amount' => 0,
            'items' => [
                [
                    'price' => 1,
                    'price_incl_tax' => 1.08,
                    'row_total' => 10,
                    'taxable_amount' => 10,
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => 0.75,
                ],
            ],
        ];

        $oneProductInclTax = $baseQuote;
        $oneProductInclTax['items'][] = [
            'code' => 'sku_1',
            'type' => 'product',
            'quantity' => 10,
            'unit_price' => 1.0,
            'tax_class_id' => 'DefaultProductClass',
            'tax_included' => true,
        ];
        $oneProductInclTaxResults = [
            'subtotal' => 9.3,
            'tax_amount' => 0.7,
            'discount_amount' => 0,
            'items' => [
                [
                    'price' => 0.93,
                    'price_incl_tax' => 1.0,
                    'row_total' => 9.3,
                    'taxable_amount' => 10,  // Shouldn't this be 9.3?
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => .7,
                ],
            ],
        ];

        $oneProductInclTaxDiffRate = $baseQuote;
        $oneProductInclTaxDiffRate['items'][] = [
            'code' => 'sku_1',
            'type' => 'product',
            'quantity' => 9,
            'unit_price' => 0.33, // this is including the store tax of 10%. Pre tax is 0.3
            'tax_class_id' => 'HigherProductClass',
            'tax_included' => true,
        ];
        $oneProductInclTaxDiffRateResults = [
            'subtotal' => 2.73,
            'tax_amount' => 0.6,
            'discount_amount' => 0,
            'items' => [
                [
                    'price' => 0.3,
                    'price_incl_tax' => 0.37,
                    'row_total' => 2.73,
                    'taxable_amount' => 3.33, // Shouldn't this match row_total?
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 22.0,
                    'row_tax' => 0.6,
                ],
            ],
        ];

        $twoProducts = $baseQuote;
        $twoProducts['items'] = [
            [
                'code' => 'sku_1',
                'type' => 'product',
                'quantity' => 10,
                'unit_price' => 1,
                'tax_class_id' => 'DefaultProductClass',
            ],
            [
                'code' => 'sku_2',
                'type' => 'product',
                'quantity' => 20,
                'unit_price' => 11,
                'tax_class_id' => 'DefaultProductClass',
            ]
        ];
        $twoProductsResults = [
            'subtotal' => 230,
            'tax_amount' => 17.25,
            'discount_amount' => 0,
            'items' => [
                [
                    'price' => 1,
                    'price_incl_tax' => 1.08,
                    'row_total' => 10,
                    'taxable_amount' => 10,
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => .75,
                ],
                [
                    'price' => 11,
                    'price_incl_tax' => 11.83,
                    'row_total' => 220,
                    'taxable_amount' => 220,
                    'code' => 'sku_2',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => 16.5,
                ],
            ],
        ];

        $twoProductsInclTax = $baseQuote;
        $twoProductsInclTax['items'] = [
            [
                'code' => 'sku_1',
                'type' => 'product',
                'quantity' => 10,
                'unit_price' => 0.98,
                'tax_class_id' => 'DefaultProductClass',
                'tax_included' => true,
            ],
            [
                'code' => 'sku_2',
                'type' => 'product',
                'quantity' => 20,
                'unit_price' => 11.99,
                'tax_class_id' => 'DefaultProductClass',
                'tax_included' => true,
            ]
        ];
        $twoProductInclTaxResults = [
            'subtotal' => 232.19,
            'tax_amount' => 17.41,
            'discount_amount' => 0,
            'items' => [
                [
                    'price' => 0.91,
                    'price_incl_tax' => 0.98,
                    'row_total' => 9.12,
                    'taxable_amount' => 9.8,  // Shouldn't this match row_total?
                    'code' => 'sku_1',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => .68,
                ],
                [
                    'price' => 11.15,
                    'price_incl_tax' => 11.99,
                    'row_total' => 223.07,
                    'taxable_amount' => 239.8, // Shouldn't this be 223.07?
                    'code' => 'sku_2',
                    'type' => 'product',
                    'tax_percent' => 7.5,
                    'row_tax' => 16.73,
                ],
            ],
        ];

        $oneProductWithChildren = $baseQuote;
        $oneProductWithChildren['items'] = [
            [
                'code' => 'child_1_sku',
                'type' => 'product',
                'quantity' => 2,
                'unit_price' => 12.34,
                'tax_class_id' => 'DefaultProductClass',
                'parent_code' => 'parent_sku',
            ],
            [
                'code' => 'parent_sku', // Put the parent in the middle of the children to test an edge case
                'type' => 'product',
                'quantity' => 10,
                'unit_price' => 0,
                'tax_class_id' => 'DefaultProductClass',
            ],
            [
                'code' => 'child_2_sku',
                'type' => 'product',
                'quantity' => 2,
                'unit_price' => 1.99,
                'tax_class_id' => 'HigherProductClass',
                'parent_code' => 'parent_sku',
            ],
        ];
        $oneProductWithChildrenResults = [
            'subtotal' => 286.6,
            'tax_amount' => 27.27,
            'discount_amount' => 0,
            'items' => [
                [
                    'code' => 'parent_sku',
                    'price' => 28.66,
                    'price_incl_tax' => 31.39,
                    'row_total' => 286.6,
                    'taxable_amount' => 286.6,
                    'type' => 'product',
                    'row_tax' => 27.27,
                ],
            ],
        ];

        return [
            'one product' => [
                'quote_details' => $oneProduct,
                'expected_tax_details' => $oneProductResults,
            ],
            'one product, tax included' => [
                'quote_details' => $oneProductInclTax,
                'expected_tax_details' => $oneProductInclTaxResults,
            ],
            'one product, tax included but differs from store rate' => [
                'quote_details' => $oneProductInclTaxDiffRate,
                'expected_tax_details' => $oneProductInclTaxDiffRateResults,
            ],
            'two products' => [
                'quote_details' => $twoProducts,
                'expected_tax_details' => $twoProductsResults,
            ],
            'two products, tax included' => [
                'quote_details' => $twoProductsInclTax,
                'expected_tax_details' => $twoProductInclTaxResults,
            ],
            'one product with two children' => [
                'quote_details' => $oneProductWithChildren,
                'expected_tax_details' => $oneProductWithChildrenResults,
            ],
        ];
    }

    /**
     * Substitutes an ID for the name of a tax class in a tax class ID field.
     *
     * @param array $data
     * @return array
     */
    private function performTaxClassSubstitution($data)
    {
        array_walk_recursive(
            $data,
            function (&$value, $key) {
                if ( ($key === 'tax_class_id' || $key === 'customer_tax_class_id')
                    && is_string($value)
                ) {
                    $value = $this->taxClasses[$value];
                }
            }
        );

        return $data;
    }

    /**
     * Helper function that sets up some default rules
     */
    private function setUpDefaultRules()
    {
        $this->taxClasses = $this->taxRuleFixtureFactory->createTaxClasses([
            ['name' => 'DefaultCustomerClass', 'type' => ClassModel::TAX_CLASS_TYPE_CUSTOMER],
            ['name' => 'DefaultProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
            ['name' => 'HigherProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
        ]);

        $this->taxRates = $this->taxRuleFixtureFactory->createTaxRates([
            ['percentage' => 7.5, 'country' => 'US', 'region' => 42],
            ['percentage' => 7.5, 'country' => 'US', 'region' => 12], // Default store rate
        ]);

        $higherRates = $this->taxRuleFixtureFactory->createTaxRates([
            ['percentage' => 22, 'country' => 'US', 'region' => 42],
            ['percentage' => 10, 'country' => 'US', 'region' => 12], // Default store rate
            ]);

        $this->taxRules = $this->taxRuleFixtureFactory->createTaxRules([
            [
                'code' => 'Default Rule',
                'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                'product_tax_class_ids' => [$this->taxClasses['DefaultProductClass']],
                'tax_rate_ids' => array_values($this->taxRates),
                'sort_order' => 0,
                'priority' => 0,
            ],
            [
                'code' => 'Higher Rate Rule',
                'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                'product_tax_class_ids' => [$this->taxClasses['HigherProductClass']],
                'tax_rate_ids' => array_values($higherRates),
                'sort_order' => 0,
                'priority' => 0,
            ],
        ]);

        // For cleanup
        $this->taxRates = array_merge($this->taxRates, $higherRates);
    }

    /**
     * Helper function that tears down some default rules
     */
    private function tearDownDefaultRules()
    {
        $this->taxRuleFixtureFactory->deleteTaxRules(array_values($this->taxRules));
        $this->taxRuleFixtureFactory->deleteTaxRates(array_values($this->taxRates));
        $this->taxRuleFixtureFactory->deleteTaxClasses(array_values($this->taxClasses));
    }

    /**
     * @return array
     */
    private function getBaseQuoteData()
    {
        $baseQuote = [
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
            'items' => [],
            'customer_tax_class_id' => 'DefaultCustomerClass',
        ];
        return $baseQuote;
    }
}
