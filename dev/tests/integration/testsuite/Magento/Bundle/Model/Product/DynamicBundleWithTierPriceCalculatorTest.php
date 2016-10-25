<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
 * @magentoAppArea frontend
 */
class DynamicBundleWithTierPriceCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\Bootstrap */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductTierPriceInterfaceFactory */
    private $tierPriceFactory;

    private $fixtureForTierPrice = [
        'data' => [
            'customer_group_id' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
            'qty' => 1,
            'value' => 50
        ]
    ];

    private $fixtureForProductOption = [
        'title' => 'Some title',
        'required' => true,
        'type' => 'checkbox'
    ];

    private $fixtureForProductOptionSelection = [
        'sku' => null,          // need to set this
        'option_id' => null,    // need to set this
        'qty' => 1,
        'is_default' => true,
        'can_change_quantity' => 0
    ];

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->tierPriceFactory = $this->objectManager->create(ProductTierPriceInterfaceFactory::class);
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
        $this->addTierPrice($bundleProduct);

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

            'Testing product price with tier price and sub items Configuration #1' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration1(),
                'expectedResults' => [
                    // 0.5 * 10
                    'minimalPrice' => 5,

                    // 0.5 * 10
                    'maximalPrice' => 5
                ]
            ],

            'Testing product price with tier price and sub items Configuration #2' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration2(),
                'expectedResults' => [
                    // 0.5 * 2 * 10
                    'minimalPrice' => 10,

                    // 0.5 * 2 * 10
                    'maximalPrice' => 10
                ]
            ],

            'Testing product price with tier price and sub items Configuration #3' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration3(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,

                    // 0.5 * (1 * 10 + 3 * 20)
                    'maximalPrice' => 35
                ]
            ],

            'Testing product price with tier price and sub items Configuration #4' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration4(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,

                    // 0.5 * (1 * 10 + 3 * 20)
                    'maximalPrice' => 35
                ]
            ],

            'Testing product price with tier price and sub items Configuration #5' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration5(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,

                    // 0.5 * 3 * 20
                    'maximalPrice' => 30
                ]
            ],

            'Testing product price with tier price and sub items Configuration #6' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration6(),
                'expectedResults' => [
                    // 0.5 * (1 * 10 + 1 * 10)
                    'minimalPrice' => 10,

                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],

            'Testing product price with tier price and sub items Configuration #7' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration7(),
                'expectedResults' => [
                    // 0.5 * (1 * 10)
                    'minimalPrice' => 5,

                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],

            'Testing product price with tier price and sub items Configuration #8' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration8(),
                'expectedResults' => [
                    // 0.5 * (1 * 10)
                    'minimalPrice' => 5,

                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],

            'Testing price for dynamic bundle product with tier price on it and on sub item' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategyConfiguration10(),
                'expectedResults' => [
                    // 0.5 * 1 * 2.5
                    'minimalPrice' => 1.25,

                    // 0.5 * 3 * 20
                    'maximalPrice' => 30
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration1()
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
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    public function getProductWithSubItemsAndOptionsStrategyConfiguration2()
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration3()
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration4()
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration5()
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration6()
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration7()
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration8()
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

    public function getProductWithSubItemsAndOptionsStrategyConfiguration10()
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
                'modifierName' => 'addTierPriceForSimple',
                'data' => ['simple1', 2.5]
            ],
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

    protected function addTierPriceForSimple(\Magento\Catalog\Model\Product $bundleProduct, $sku, $price)
    {
        $simple = $this->productRepository->get($sku, false, null, true);
        $fixtureForTierPrice = $this->fixtureForTierPrice;
        $fixtureForTierPrice['data']['value'] = $price;
        $tierPrice = $this->tierPriceFactory->create($fixtureForTierPrice);
        $simple->setTierPrices([$tierPrice]);
        $this->productRepository->save($simple);

        return $bundleProduct;
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

    private function addTierPrice(\Magento\Catalog\Model\Product $bundleProduct)
    {
        $tierPrice = $this->tierPriceFactory->create($this->fixtureForTierPrice);
        $bundleProduct->setTierPrices([$tierPrice]);

        return $bundleProduct;
    }
}
