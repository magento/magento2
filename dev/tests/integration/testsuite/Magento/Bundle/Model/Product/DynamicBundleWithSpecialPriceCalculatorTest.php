<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product_with_special_price.php
 * @magentoAppArea frontend
 */
class DynamicBundleWithSpecialPriceCalculatorTest extends BundlePriceAbstract
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

        if (isset($expectedResults['regularMinimalPrice'])) {
            $priceCode = \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE;
            $this->assertEquals(
                $expectedResults['regularMinimalPrice'],
                $priceInfo->getPrice($priceCode)->getMinimalPrice()->getValue(),
                'Failed to check minimal regular price on product'
            );
        }

        if (isset($expectedResults['regularMaximalPrice'])) {
            $priceCode = \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE;
            $this->assertEquals(
                $expectedResults['regularMaximalPrice'],
                $priceInfo->getPrice($priceCode)->getMaximalPrice()->getValue(),
                'Failed to check maximal regular price on product'
            );
        }
    }

    /**
     * Test cases for current test
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTestCases()
    {
        return [
            '#1 Testing price for dynamic bundle with one required option and special price' => [
                'strategy' => $this->getBundleConfiguration1(),
                'expectedResults' => [
                    // 0.5 * 10
                    'minimalPrice' => 5,
                    // 0.5 * 10
                    'maximalPrice' => 5
                ]
            ],

            '#2 Testing price for dynamic bundle with one non required option and special price' => [
                'strategy' => $this->getBundleConfiguration2(),
                'expectedResults' => [
                    // 0.5 * 2 * 10
                    'minimalPrice' => 10,
                    // 0.5 * 2 * 10
                    'maximalPrice' => 10
                ]
            ],

            '
                #3 Testing price for dynamic bundle 
                with one required checkbox type option, two simples and special price
            ' => [
                'strategy' => $this->getBundleConfiguration3(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,
                    // 0.5 * (1 * 10 + 3 * 30)
                    'maximalPrice' => 50
                ]
            ],

            '
                #4 Testing price for dynamic bundle 
                with one required multi type option, two simples with special price
            ' => [
                'strategy' => $this->getBundleConfiguration4(),
                'expectedResults' => [
                    // 0.5 * (min (1 * 9.9, 2.5 * 4))
                    'minimalPrice' => 4.95,
                    // 0.5 * ( 1 * 9.9 +  2.5 * 4)
                    'maximalPrice' => 9.95
                ]
            ],

            '#5 Testing price for dynamic bundle with one required option, one non required and special price' => [
                'strategy' => $this->getBundleConfiguration5(),
                'expectedResults' => [
                    // 0.5 * (3 * 2.5)
                    'minimalPrice' => 3.75,
                    // 0.5 * (3 * 13 + 1 * 30 + 1 * 10)
                    'maximalPrice' => 39.5,
                    // 1 * 10
                    'regularMinimalPrice' => '10',
                    // 3 * 20 + (30 * 1 + 13 * 3)
                    'regularMaximalPrice' => '129',
                ]
            ],

            '#6 Testing price for dynamic bundle with one simple product with special price' => [
                'strategy' => $this->getBundleConfiguration6(),
                'expectedResults' => [
                    // 0.5 * min(4 * 2.5, 1 * 9.9)
                    'minimalPrice' => 4.95,
                    // 0.5 * max(4 * 2.5, 1 * 9.9)
                    'maximalPrice' => 5
                ]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required option
     * @return array
     */
    private function getBundleConfiguration1()
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
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one non required option and special price
     * @return array
     */
    private function getBundleConfiguration2()
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
     * Dynamic bundle with one required checkbox type option, two simples and special price
     * @return array
     */
    private function getBundleConfiguration3()
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
                    [
                        'sku' => 'simple3',
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
     * Dynamic bundle with one required multi type option, two simples and special price
     * @return array
     */
    private function getBundleConfiguration4()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple5',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 4,
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
     * Dynamic bundle with one required option, one non required and special price
     * @return array
     */
    private function getBundleConfiguration5()
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
                'required' => false,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple3',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple4',
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
     * Dynamic bundle with one simple product with special price
     * @return array
     */
    private function getBundleConfiguration6()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple2',
                        'qty' => 4,
                    ],
                    [
                        'sku' => 'simple5',
                        'qty' => 1,
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
