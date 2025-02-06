<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * @codingStandardsIgnoreStart
 * @magentoDataFixtureBeforeTransaction Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product_with_catalog_rule.php
 * @codingStandardsIgnoreEnd
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class DynamicBundleWithCatalogPriceRuleCalculatorTest extends BundlePriceAbstract
{
    /**
     * @param array $strategyModifiers
     * @param array $expectedResults
     * @dataProvider getTestCases
     * @magentoAppIsolation enabled
     */
    public function testPriceForDynamicBundle(array $strategyModifiers, array $expectedResults)
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
    public static function getTestCases()
    {
        return [
            '#1 Testing price for dynamic bundle with one required option' => [
                'strategyModifiers' => self::getBundleProductConfiguration1(),
                'expectedResults' => [
                    // 10 * 0.9
                    'minimalPrice' => 9,

                    // 10 * 0.9
                    'maximalPrice' => 9
                ]
            ],

            '#3 Testing price for dynamic bundle with one non required option' => [
                'strategyModifiers' => self::getBundleProductConfiguration3(),
                'expectedResults' => [
                    // 0.9 * 2 * 10
                    'minimalPrice' => 18,

                    // 0.9 * 2 * 10
                    'maximalPrice' => 18
                ]
            ],

            '#4 Testing price for dynamic bundle with one required checkbox type option and 2 simples' => [
                'strategyModifiers' => self::getBundleProductConfiguration4(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 1 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 63
                ]
            ],

            '#5 Testing price for dynamic bundle with one required multi type option and 2 simples' => [
                'strategyModifiers' => self::getBundleProductConfiguration5(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 1 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 63
                ]
            ],

            '#6 Testing price for dynamic bundle with one required radio type option and 2 simples' => [
                'strategyModifiers' => self::getBundleProductConfiguration6(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 3 * 20
                    'maximalPrice' => 54
                ]
            ],

            '#7 Testing price for dynamic bundle with two required options' => [
                'strategyModifiers' => self::getBundleProductConfiguration7(),
                'expectedResults' => [
                    // 0.9 * 1 * 10 + 0.9 * 1 * 10
                    'minimalPrice' => 18,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],

            '#8 Testing price for dynamic bundle with one required option and one non required' => [
                'strategyModifiers' => self::getBundleProductConfiguration8(),
                'expectedResults' => [
                    // 1 * 0.9 * 10
                    'minimalPrice' => 9,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],

            '#9 Testing price for dynamic bundle with two non required options' => [
                'strategyModifiers' => self::getBundleProductConfiguration9(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required option
     * @return array
     */
    private static function getBundleProductConfiguration1()
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
                    ],
                ]
            ],
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one non required option
     * @return array
     */
    private static function getBundleProductConfiguration3()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'type' => 'checkbox',
                'required' => false,
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 2,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required checkbox type option and 2 simples
     * @return array
     */
    private static function getBundleProductConfiguration4()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'type' => 'checkbox',
                'required' => true,
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required multi type option and 2 simples
     * @return array
     */
    private static function getBundleProductConfiguration5()
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
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required radio type option and 2 simples
     * @return array
     */
    private static function getBundleProductConfiguration6()
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
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with two required options
     * @return array
     */
    private static function getBundleProductConfiguration7()
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
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
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
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required option and one non required
     * @return array
     */
    private static function getBundleProductConfiguration8()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => false,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
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
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with two non required options
     * @return array
     */
    private static function getBundleProductConfiguration9()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => false,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'title' => 'Op2',
                'required' => false,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }
}
