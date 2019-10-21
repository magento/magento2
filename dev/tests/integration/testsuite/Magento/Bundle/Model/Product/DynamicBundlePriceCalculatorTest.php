<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * @magentoAppArea frontend
 */
class DynamicBundlePriceCalculatorTest extends BundlePriceAbstract
{
    /**
     * @param array $strategyModifiers
     * @param array $expectedResults
     * @dataProvider getTestCases
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
     * @magentoDbIsolation disabled
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

        $priceInfoFromIndexer = $this->productCollectionFactory->create()
            ->addFieldToFilter('sku', 'bundle_product')
            ->addPriceData()
            ->load()
            ->getFirstItem();

        $this->assertEquals($expectedResults['minimalPrice'], $priceInfoFromIndexer->getMinimalPrice());
        $this->assertEquals($expectedResults['maximalPrice'], $priceInfoFromIndexer->getMaxPrice());
    }

    /**
     * @param array $strategyModifiers
     * @param array $expectedResults
     * @dataProvider getTestCases
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
     * @magentoDbIsolation disabled
     */
    public function testPriceForDynamicBundleInWebsiteScope(array $strategyModifiers, array $expectedResults)
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

        $priceInfoFromIndexer = $this->productCollectionFactory->create()
            ->addFieldToFilter('sku', 'bundle_product')
            ->addPriceData()
            ->load()
            ->getFirstItem();

        $this->assertEquals($expectedResults['minimalPrice'], $priceInfoFromIndexer->getMinimalPrice());
        $this->assertEquals($expectedResults['maximalPrice'], $priceInfoFromIndexer->getMaxPrice());
    }

    /**
     * Test cases for current test
     * @return array
     */
    public function getTestCases()
    {
        return [
            '#1 Testing price for dynamic bundle product with one simple' => [
                'strategy' => $this->getBundleConfiguration1(),
                'expectedResults' => [
                    // just price from simple1
                    'minimalPrice' => 10,
                    // just price from simple1
                    'maximalPrice' => 10
                ]
            ],

            '#2 Testing price for dynamic bundle product with three simples and different qty' => [
                'strategy' => $this->getBundleConfiguration2(),
                'expectedResults' => [
                    // min price from simples 3*10 or 30
                    'minimalPrice' => 30,
                    // (3 * 10) + (2 * 20) + 30
                    'maximalPrice' => 100
                ]
            ],

            '#3 Testing price for dynamic bundle product with four simples and different price' => [
                'strategy' => $this->getBundleConfiguration3(),
                'expectedResults' => [
                    //  10
                    'minimalPrice' => 10,
                    // 10 + 20 + 30
                    'maximalPrice' => 60
                ]
            ],

            '#4 Testing price for dynamic bundle with two non required options' => [
                'strategy' => $this->getBundleConfiguration4(),
                'expectedResults' => [
                    // 1 * 10
                    'minimalPrice' => 10,
                    // 3 * 20 + 1 * 10 + 3 * 20
                    'maximalPrice' => 130
                ]
            ],

            '#5 Testing price for dynamic bundle with two required options' => [
                'strategy' => $this->getBundleConfiguration5(),
                'expectedResults' => [
                    // 1 * 10 + 1 * 10
                    'minimalPrice' => 20,
                    // 3 * 20 + 1 * 10 + 3 * 20
                    'maximalPrice' => 130
                ]
            ],
        ];
    }

    /**
     * Dynamic bundle product with one simple
     *
     * @return array
     */
    private function getBundleConfiguration1()
    {
        $optionsData = [
            [
                'title' => 'op1',
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
     * Dynamic bundle product with three simples and different qty
     *
     * @return array
     */
    private function getBundleConfiguration2()
    {
        $optionsData = [
            [
                'title' => 'op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 3,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 2,
                    ],
                    [
                        'sku' => 'simple3',
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
     * Dynamic bundle product with three simples and different price
     *
     * @return array
     */
    private function getBundleConfiguration3()
    {
        $optionsData = [
            [
                'title' => 'op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple3',
                        'qty' => 1,
                    ]
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
     * Dynamic bundle with two non required options and special price
     * @return array
     */
    private function getBundleConfiguration4()
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
     * Dynamic bundle with two required options
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
}
