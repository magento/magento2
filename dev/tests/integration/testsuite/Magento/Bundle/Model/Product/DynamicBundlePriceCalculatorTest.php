<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
 * @magentoAppArea frontend
 */
class DynamicBundlePriceCalculatorTest extends BundlePrice
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
            'Testing price for dynamic bundle product with one simple' => [
                'strategy' => $this->getProductWithOneSimple(),
                'expectedResults' => [
                    // just price from simple1
                    'minimalPrice' => 10,
                    // just price from simple1
                    'maximalPrice' => 10
                ]
            ],
            'Testing price for dynamic bundle product with three simples and differnt qty' => [
                'strategy' => $this->getProductWithDifferentQty(),
                'expectedResults' => [
                    // min price from simples 3*10 or 30
                    'minimalPrice' => 30,
                    // (3 * 10) + (2 * 20) + 30
                    'maximalPrice' => 100
                ]
            ],
            'Testing price for dynamic bundle product with four simples and differnt price' => [
                'strategy' => $this->getProductWithDifferentPrice(),
                'expectedResults' => [
                    //  10
                    'minimalPrice' => 10,
                    // 10 + 20 + 30
                    'maximalPrice' => 60
                ]
            ]
        ];
    }

    public function getProductWithOneSimple()
    {
        $optionsData = [
            [
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

    public function getProductWithDifferentQty()
    {
        $optionsData = [
            [
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

    public function getProductWithDifferentPrice()
    {
        $optionsData = [
            [
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
}
