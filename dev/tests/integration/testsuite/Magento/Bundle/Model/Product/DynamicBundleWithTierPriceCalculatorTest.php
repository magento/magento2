<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
 * @magentoAppArea frontend
 */
class DynamicBundleWithTierPriceCalculatorTest extends BundlePriceAbstract
{
    /** @var ProductTierPriceInterfaceFactory */
    private $tierPriceFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->tierPriceFactory = $this->objectManager->create(ProductTierPriceInterfaceFactory::class);
    }

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
     */
    public function getTestCases()
    {
        return [
            '#1 Testing product price with tier price and sub items Configuration #1' => [
                'strategy' => $this->getBundleConfiguration1(),
                'expectedResults' => [
                    // 0.5 * 10
                    'minimalPrice' => 5,
                    // 0.5 * 10
                    'maximalPrice' => 5
                ]
            ],
            '#2 Testing product price with tier price and sub items Configuration #2' => [
                'strategy' => $this->getBundleConfiguration2(),
                'expectedResults' => [
                    // 0.5 * 2 * 10
                    'minimalPrice' => 10,
                    // 0.5 * 2 * 10
                    'maximalPrice' => 10
                ]
            ],
            '#3 Testing product price with tier price and sub items Configuration #3' => [
                'strategy' => $this->getBundleConfiguration3(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,
                    // 0.5 * (1 * 10 + 3 * 20)
                    'maximalPrice' => 35
                ]
            ],
            '#4 Testing product price with tier price and sub items Configuration #4' => [
                'strategy' => $this->getBundleConfiguration4(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,
                    // 0.5 * (1 * 10 + 3 * 20)
                    'maximalPrice' => 35
                ]
            ],
            '#5 Testing product price with tier price and sub items Configuration #5' => [
                'strategy' => $this->getBundleConfiguration5(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,
                    // 0.5 * 3 * 20
                    'maximalPrice' => 30
                ]
            ],
            '#6 Testing product price with tier price and sub items Configuration #6' => [
                'strategy' => $this->getBundleConfiguration6(),
                'expectedResults' => [
                    // 0.5 * (1 * 10 + 1 * 10)
                    'minimalPrice' => 10,
                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],
            '#7 Testing product price with tier price and sub items Configuration #7' => [
                'strategy' => $this->getBundleConfiguration7(),
                'expectedResults' => [
                    // 0.5 * (1 * 10)
                    'minimalPrice' => 5,
                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],
            '#8 Testing product price with tier price and sub items Configuration #8' => [
                'strategy' => $this->getBundleConfiguration8(),
                'expectedResults' => [
                    // 0.5 * (1 * 10)
                    'minimalPrice' => 5,
                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],
            '#9 Testing price for dynamic bundle product with tier price on it and on sub item' => [
                'strategy' => $this->getBundleConfiguration10(),
                'expectedResults' => [
                    // 0.5 * 1 * 2.5
                    'minimalPrice' => 1.25,
                    // 0.5 * 3 * 20
                    'maximalPrice' => 30
                ]
            ],
        ];
    }

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

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

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

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

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
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    private function getBundleConfiguration4()
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

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

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
            ]
        ];

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    private function getBundleConfiguration6()
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

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    private function getBundleConfiguration7()
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

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    private function getBundleConfiguration8()
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

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    private function getBundleConfiguration10()
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

        $tierPriceData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ];

        $tierPriceSimpleProductData = [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 2.5
        ];

        return [
            [
                'modifierName' => 'addTierPrice',
                'data' => [$tierPriceData]
            ],
            [
                'modifierName' => 'addTierPriceForSimple',
                'data' => ['simple1', $tierPriceSimpleProductData]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $tirePriceData
     * @return \Magento\Catalog\Model\Product
     */
    protected function addTierPrice(\Magento\Catalog\Model\Product $product, $tirePriceData)
    {
        $tierPrice = $this->tierPriceFactory->create([
            'data' => $tirePriceData
        ]);
        $product->setTierPrices([$tierPrice]);

        return $product;
    }

    /**
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param string $sku
     * @param array $tirePriceData
     * @return \Magento\Catalog\Model\Product
     */
    protected function addTierPriceForSimple(\Magento\Catalog\Model\Product $bundleProduct, $sku, $tirePriceData)
    {
        $simple = $this->productRepository->get($sku, false, null, true);
        $simple = $this->addTierPrice($simple, $tirePriceData);
        $this->productRepository->save($simple);

        return $bundleProduct;
    }
}
