<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product_with_catalog_rule.php
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
        $bundleProduct = $this->prepareFixture($strategyModifiers);

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

    public function getTestCases()
    {
        return [
            'Testing price for dynamic bundle product with sub items and catalog rule price' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(),
                'expectedResults' => [
                    // 10 * 0.9
                    'minimalPrice' => 9,

                    // 10 * 0.9
                    'maximalPrice' => 9
                ]
            ],

            'Testing price for dynamic bundle product with special price, sub items and catalog rule price' => [
                'strategy' => $this->getBundleProductConfiguration1(),
                'expectedResults' => [
                    // 0.5 * 10 * 0.9
                    'minimalPrice' => 4.5,

                    // 0.5 * 10 * 0.9
                    'maximalPrice' => 4.5
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #2' => [
                'strategy' => $this->getBundleProductConfiguration2(),
                'expectedResults' => [
                    // 0.9 * 2 * 10
                    'minimalPrice' => 18,

                    // 0.9 * 2 * 10
                    'maximalPrice' => 18
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #3' => [
                'strategy' => $this->getBundleProductConfiguration3(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 1 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 63
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #4' => [
                'strategy' => $this->getBundleProductConfiguration4(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 1 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 63
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #5' => [
                'strategy' => $this->getBundleProductConfiguration5(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 3 * 20
                    'maximalPrice' => 54
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #6' => [
                'strategy' => $this->getBundleProductConfiguration6(),
                'expectedResults' => [
                    // 0.9 * 1 * 10 + 0.9 * 1 * 10
                    'minimalPrice' => 18,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #7' => [
                'strategy' => $this->getBundleProductConfiguration7(),
                'expectedResults' => [
                    // 1 * 0.9 * 10
                    'minimalPrice' => 9,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #8' => [
                'strategy' => $this->getBundleProductConfiguration8(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],
        ];
    }

    public function getProductWithSubItemsAndOptionsStrategy()
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

    public function getBundleProductConfiguration1()
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
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    public function getBundleProductConfiguration2()
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

    public function getBundleProductConfiguration3()
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

    public function getBundleProductConfiguration4()
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

    public function getBundleProductConfiguration5()
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

    public function getBundleProductConfiguration6()
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

    public function getBundleProductConfiguration7()
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

    public function getBundleProductConfiguration8()
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

    /**
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param int $discount
     * @return \Magento\Catalog\Model\Product
     */
    protected function addSpecialPrice(\Magento\Catalog\Model\Product $bundleProduct, $discount)
    {
        $bundleProduct->setSpecialPrice($discount);

        return $bundleProduct;
    }
}
