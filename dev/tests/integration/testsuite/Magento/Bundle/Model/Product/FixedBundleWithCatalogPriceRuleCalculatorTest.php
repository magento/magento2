<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use \Magento\Bundle\Api\Data\LinkInterface;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product_with_catalog_rule.php
 * @magentoAppArea frontend
 */
class FixedBundleWithCatalogPriceRuleCalculatorTest extends BundlePriceAbstract
{
    /**
     * @param array $strategyModifiers
     * @param array $expectedResults
     * @dataProvider getTestCases
     * @magentoAppIsolation enabled
     */
    public function testPriceForFixedBundle(array $strategyModifiers, array $expectedResults)
    {
        $this->prepareFixture($strategyModifiers, 'bundle_product');
        $bundleProduct = $this->productRepository->get('bundle_product', false, null, true);

        /** @var \Magento\Framework\Pricing\PriceInfo\Base $priceInfo */
        $priceInfo = $bundleProduct->getPriceInfo();
        $priceCode = \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE;

        $this->assertEquals(
            $expectedResults['minimalPrice'],
            $priceInfo->getPrice($priceCode)->getMinimalPrice()->getValue(),
            'Failed to check minimal price on product'
        );

        $this->assertEquals(
            $expectedResults['maximalPrice'],
            $priceInfo->getPrice($priceCode)->getMaximalPrice()->getValue(),
            'Failed to check maximal price on product'
        );
    }

    /**
     * Test cases for current test
     * @return array
     */
    public function getTestCases()
    {
        return [
            '
                #1 Testing price for fixed bundle product
                with catalog price rule and without sub items and options
            ' => [
                'strategy' => $this->getEmptyProductStrategy(),
                'expectedResults' => [
                    // 110 * 0.9
                    'minimalPrice' => 99,

                    // 110 * 0.9
                    'maximalPrice' => 99
                ]
            ],

            '
                #2 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 20 + 100
                    'minimalPrice' => 219,

                    // 0.9 * 110 + 1 * 20 + 100
                    'maximalPrice' => 219
                ]
            ],

            '
                #3 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 0.9 * 110 * 1
                    'minimalPrice' => 217.8,

                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 0.9 * 110 * 1
                    'maximalPrice' => 217.8
                ]
            ],

            '
                #4 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 20 + 0.9 * 110 * 1
                   'minimalPrice' => 218,

                    // 0.9 * 110 + 1 * 20 + 0.9 * 110 * 1
                   'maximalPrice' => 218
                ]
            ],

            '
                #5 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 100
                   'minimalPrice' => 218.8,

                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 100
                   'maximalPrice' => 218.8
                ]
            ],

            '
                #6 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 20 + 100
                    'minimalPrice' => 219,

                    // 0.9 * 110 + 1 * 20 + 100
                    'maximalPrice' => 219
                ]
            ],

            '
                #7 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 0.9 * 110 * 1
                    'minimalPrice' => 217.8,

                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 0.9 * 110 * 1
                    'maximalPrice' => 217.8
                ]
            ],

            '
                #8 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 20 + 0.9 * 110 * 1
                    'minimalPrice' => 218,

                    // 0.9 * 110 + 1 * 20 + 0.9 * 110 * 1
                    'maximalPrice' => 218
                ]
            ],

            '
                #9 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 100
                    'minimalPrice' => 218.8,

                    // 0.9 * 110 + 0.9 * 110 * 0.2 + 100
                    'maximalPrice' => 218.8
                ]
            ],

            '
                #10 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 100
                    'minimalPrice' => 199,

                    // 0.9 * 110 + 2 * 20 + 100
                    'maximalPrice' => 239
                ]
            ],

            '
                #11 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 0.9 * 110 * 1
                    'minimalPrice' => 198,

                    // 0.9 * 110 + 2 * 0.9 * 110 * 0.2 + 1 * 0.9 * 110
                    'maximalPrice' => 237.6
                ]
            ],

            '
                #12 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 0.9 * 110
                    'minimalPrice' => 198,

                    // 0.9 * 110 + 2 * 20 + 1 * 0.9 * 110
                    'maximalPrice' => 238
                ]
            ],

            '
                #13 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 100
                    'minimalPrice' => 199,

                    // 0.9 * 110 + 2 * 0.2 * 0.9 *  110 + 100
                    'maximalPrice' => 238.6
                ]
            ],

            '
                #14 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 3 * 10 + 100
                    'minimalPrice' => 229,

                    // 0.9 * 110 + 3 * 10 + 1 * 40 + 100
                    'maximalPrice' => 269
                ]
            ],

            '
                #15 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 3 * 0.9 * 110 * 0.1 + 0.9 * 110 * 1
                    'minimalPrice' => 227.7,

                    // 0.9 * 110 + 3 * 0.9 * 110 * 0.1 + 1 * 0.9 * 110 * 0.4 + 0.9 * 110 * 1
                    'maximalPrice' => 267.3
                ]
            ],

            '
                #16 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 3 * 10 + 1 * 0.9 * 110
                    'minimalPrice' => 228,

                    // 0.9 * 110 + 3 * 10 + 1 * 40 + 1 * 0.9 * 110
                    'maximalPrice' => 268
                ]
            ],

            '
                #17 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 3 * 0.9 *  110 * 0.1 + 100
                    'minimalPrice' => 228.7,

                    // 0.9 * 110 + 3 * 0.9 * 110 * 0.1 + 1 * 0.9 * 110 * 0.4 + 100
                    'maximalPrice' => 268.3
                ]
            ],

            '
                #18 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 40 + 100
                    'minimalPrice' => 239,

                    // 0.9 * 110 + 1 * 40 + 3 * 15 + 100
                    'maximalPrice' => 284
                ]
            ],

            '
                #19 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 1 * 0.9 * 110
                    'minimalPrice' => 237.6,

                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 3 * 0.9 * 110 * 0.15 + 0.9 * 110 * 1
                    'maximalPrice' => 282.15
                ]
            ],

            '
                #20 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 40 + 1 * 0.9 * 110
                    'minimalPrice' => 238,

                    // 0.9 * 110 + 1 * 40 + 3 * 15 + 1 * 0.9 * 110
                    'maximalPrice' => 283
                ]
            ],

            '
                #21 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 100
                    'minimalPrice' => 238.6,

                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 3 * 0.9 * 110 * 0.15 + 100
                    'maximalPrice' => 283.15
                ]
            ],

            '
                #22 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 40 + 100
                    'minimalPrice' => 239,

                    // 0.9 * 110 + 3 * 15 + 100
                    'maximalPrice' => 244
                ]
            ],

            '
                #23 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 1 * 0.9 * 110
                    'minimalPrice' => 237.6,

                    // 0.9 * 110 + 3 * 0.9 * 110 * 0.15 + 1 * 0.9 * 110
                    'maximalPrice' => 242.55
                ]
            ],

            '
                #24 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 40 + 0.9 * 110 * 1
                    'minimalPrice' => 238,

                    // 0.9 * 110 + 3 * 15 + 0.9 * 110 * 1
                    'maximalPrice' => 243
                ]
            ],

            '
                #25 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 100
                    'minimalPrice' => 238.6,

                    // 0.9 * 110 + 3 * 0.9 * 110 * 0.15 + 100
                    'maximalPrice' => 243.55
                ]
            ],

            '
                #26 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 40 + 1 * 20 + 100
                    'minimalPrice' => 259,

                    // 0.9 * 110 + 3 * 15 + 1 * 20 + 3 * 10 + 100
                    'maximalPrice' => 294
                ]
            ],

            '
                #27 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 1 * 0.9 * 110 * 0.2 + 0.9 * 110 * 1
                    'minimalPrice' => 257.4,

                    // 0.9 * 110 + 3 * 0.9 * 110 * 0.15 + 1 * 0.9 * 110 * 0.2 + 3 * 0.9 * 110 * 0.1 + 0.9 * 110 * 1
                    'maximalPrice' => 292.05
                ]
            ],

            '
                #28 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 40 + 1 * 20 + 1 * 0.9 * 110
                    'minimalPrice' => 258,

                    // 0.9 * 110 + 3 * 15 + 1 * 20 + 3 * 10 + 1 * 0.9 * 110
                    'maximalPrice' => 293
                ]
            ],

            '
                #29 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.9 * 110 + 1 * 0.9 * 110 * 0.4 + 1 * 0.9 * 110 * 0.2 + 100
                    'minimalPrice' => 258.4,

                    // 0.9 * 110 + 3 * 0.9 * 110 * 0.15 + 1 * 0.9 * 110 * 0.2 + 3 * 0.9 * 110 * 0.1 + 100
                    'maximalPrice' => 293.05
                ]
            ],
        ];
    }

    private function getEmptyProductStrategy()
    {
        return [];
    }

    private function getProductWithSubItemsAndOptionsStrategy($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                        'price' => 20,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ],
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType,
                'title' => 'Test Field',
                'type' => 'field',
                'is_require' => 1,
                'price' => 100,
                'sku' => '1-text',
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    private function getProductSubItemsAndOptionsStrategyConfiguration1($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                        'price' => 20,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType,
                'title' => 'Test Field',
                'type' => 'field',
                'is_require' => 1,
                'price' => 100,
                'sku' => '1-text',
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    private function getProductSubItemsAndOptionsStrategyConfiguration2($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'type' => 'checkbox',
                'required' => false,
                'links' => [
                    [
                        'sku' => 'simple1',
                        'price' => 20,
                        'qty' => 2,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType,
                'title' => 'Test Field',
                'type' => 'field',
                'is_require' => 1,
                'price' => 100,
                'sku' => '1-text',
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    private function getProductSubItemsAndOptionsStrategyConfiguration3($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'price' => 10,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType,
                'title' => 'Test Field',
                'type' => 'field',
                'is_require' => 1,
                'price' => 100,
                'sku' => '1-text',
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    private function getProductSubItemsAndOptionsStrategyConfiguration4($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'multi',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'price' => 15,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType,
                'title' => 'Test Field',
                'type' => 'field',
                'is_require' => 1,
                'price' => 100,
                'sku' => '1-text',
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    private function getProductSubItemsAndOptionsStrategyConfiguration5($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'price' => 15,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType,
                'title' => 'Test Field',
                'type' => 'field',
                'is_require' => 1,
                'price' => 100,
                'sku' => '1-text',
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    private function getProductSubItemsAndOptionsStrategyConfiguration6($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'price' => 15,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ],
            [
                'title' => 'Op2',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                        'price' => 20,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'price' => 10,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType,
                'title' => 'Test Field',
                'type' => 'field',
                'is_require' => 1,
                'price' => 100,
                'sku' => '1-text',
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }
}
