<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTestCases()
    {
        return [
            '
                #1 Testing price for fixed bundle product
                with catalog price rule and without sub items and options
            ' => [
                'strategy' => $this->getBundleConfiguration1(),
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
                'strategy' => $this->getBundleConfiguration2(
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
                'strategy' => $this->getBundleConfiguration2(
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
                'strategy' => $this->getBundleConfiguration2(
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
                'strategy' => $this->getBundleConfiguration2(
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
                with catalog price rule, fixed sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration3(
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
                #7 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration3(
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
                #8 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration3(
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
                #9 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration3(
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
                #10 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration4(
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
                #11 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration4(
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
                #12 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration4(
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
                #13 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration4(
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
                #14 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration5(
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
                #15 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration5(
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
                #16 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration5(
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
                #17 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration5(
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
                #18 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration6(
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
                #19 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration6(
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
                #20 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration6(
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
                #21 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration6(
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
                #22 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration7(
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
                #23 Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration7(
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
                #24 Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options
            ' => [
                'strategy' => $this->getBundleConfiguration7(
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
                #25 Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options
            ' => [
                'strategy' => $this->getBundleConfiguration7(
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

    /**
     * Fixed bundle product with catalog price rule and without sub items and options
     * @return array
     */
    private function getBundleConfiguration1()
    {
        return [];
    }

    /**
     * Fixed bundle product with catalog price rule, one required option and one custom option
     * @param string $selectionsPriceType
     * @param string $customOptionsPriceType
     * @return array
     */
    private function getBundleConfiguration2($selectionsPriceType, $customOptionsPriceType)
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

    /**
     * Fixed bundle product with catalog price rule, one non required option and one custom option
     * @param string $selectionsPriceType
     * @param string $customOptionsPriceType
     * @return array
     */
    private function getBundleConfiguration3($selectionsPriceType, $customOptionsPriceType)
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

    /**
     * Fixed bundle product with catalog price rule, one checkbox type option with 2 simples and one custom option
     * @param string $selectionsPriceType
     * @param string $customOptionsPriceType
     * @return array
     */
    private function getBundleConfiguration4($selectionsPriceType, $customOptionsPriceType)
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

    /**
     * Fixed bundle product with catalog price rule, one multi type option with 2 simples and one custom option
     * @param string $selectionsPriceType
     * @param string $customOptionsPriceType
     * @return array
     */
    private function getBundleConfiguration5($selectionsPriceType, $customOptionsPriceType)
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

    /**
     * Fixed bundle product with catalog price rule, one radio type option with 2 simples and one custom option
     * @param string $selectionsPriceType
     * @param string $customOptionsPriceType
     * @return array
     */
    private function getBundleConfiguration6($selectionsPriceType, $customOptionsPriceType)
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

    /**
     * Fixed bundle product with catalog price rule, two required options and one custom option
     * @param string $selectionsPriceType
     * @param string $customOptionsPriceType
     * @return array
     */
    private function getBundleConfiguration7($selectionsPriceType, $customOptionsPriceType)
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
