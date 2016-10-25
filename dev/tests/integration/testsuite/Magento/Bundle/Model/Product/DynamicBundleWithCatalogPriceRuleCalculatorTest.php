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
class DynamicBundleWithCatalogPriceRuleCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\Bootstrap */
    protected $objectManager;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    protected $fixtureForProductOption = [
        'title' => 'Some title',
        'required' => true,
        'type' => 'checkbox'
    ];

    protected $fixtureForProductOptionSelection = [
        'sku' => null,          // need to set this
        'option_id' => null,    // need to set this
        'qty' => 1,
        'is_default' => true,
        'can_change_quantity' => 0
    ];

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @param $strategyModifiers array
     * @param $expectedResults array
     * @dataProvider getTestCases
     * @magentoAppIsolation enabled
     */
    public function testPriceForDynamicBundle(array $strategyModifiers, array $expectedResults)
    {
        $bundleProduct = $this->productRepository->get('spherical_horse_in_a_vacuum');

        foreach ($strategyModifiers as $modifier) {
            if (method_exists($this, $modifier['modifierName'])) {
                array_unshift($modifier['data'], $bundleProduct);
                $bundleProduct = call_user_func_array([$this, $modifier['modifierName']], $modifier['data']);
            }
        }

        $this->productRepository->save($bundleProduct);
        $bundleProduct = $this->productRepository->get('spherical_horse_in_a_vacuum', false, null, true);

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

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #2' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration2(),
                'expectedResults' => [
                    // 0.9 * 2 * 10
                    'minimalPrice' => 18,

                    // 0.9 * 2 * 10
                    'maximalPrice' => 18
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #3' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration3(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 1 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 63
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #4' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration4(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 1 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 63
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #5' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration5(),
                'expectedResults' => [
                    // 0.9 * 1 * 10
                    'minimalPrice' => 9,

                    // 0.9 * 3 * 20
                    'maximalPrice' => 54
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #6' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration6(),
                'expectedResults' => [
                    // 0.9 * 1 * 10 + 0.9 * 1 * 10
                    'minimalPrice' => 18,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #7' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration7(),
                'expectedResults' => [
                    // 1 * 0.9 * 10
                    'minimalPrice' => 9,

                    // 3 * 0.9 * 20 + 1 * 0.9 * 10 + 3 * 0.9 * 20
                    'maximalPrice' => 117
                ]
            ],

            'Testing price for dynamic bundle product with sub items and catalog rule price Configuration #8' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration8(),
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
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration1()
    {
        $optionsData = [
            [
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration2()
    {
        $optionsData = [
            [
                'required' => false,
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration3()
    {
        $optionsData = [
            [
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration4()
    {
        $optionsData = [
            [
                'type' => 'multi',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration5()
    {
        $optionsData = [
            [
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration6()
    {
        $optionsData = [
            [
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 2,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 2,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration7()
    {
        $optionsData = [
            [
                'required' => false,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 2,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 2,
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration8()
    {
        $optionsData = [
            [
                'required' => false,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'required' => false,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 2,
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 2,
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

    protected function getFixtureForProductOption(array $data = [])
    {
        $fixture = $this->fixtureForProductOption;

        // make title different for each call
        $fixture['title'] .= ' ' . microtime(true);

        return array_merge($fixture, $data);
    }

    protected function getFixtureForProductOptionSelection($data)
    {
        $fixture = $this->fixtureForProductOptionSelection;

        return array_merge($fixture, $data);
    }

    protected function addSimpleProduct(\Magento\Catalog\Model\Product $bundleProduct, array $optionsData)
    {
        $options = [];

        foreach ($optionsData as $optionData) {
            $links = [];
            $linksData = $optionData['links'];
            unset($optionData['links']);

            $option = $this->objectManager->create(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class)
                ->create(['data' => $this->getFixtureForProductOption($optionData)])
                ->setSku($bundleProduct->getSku())
                ->setOptionid(null);

            foreach ($linksData as $linkData) {
                $links[] = $this->objectManager->create(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class)
                    ->create(['data' => $this->getFixtureForProductOptionSelection($linkData)]);
            }

            $option->setProductLinks($links);
            $options[] = $option;
        }

        $extension = $bundleProduct->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $bundleProduct->setExtensionAttributes($extension);

        return $bundleProduct;
    }
}
