<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use \Magento\Bundle\Api\Data\LinkInterface;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product.php
 * @magentoAppArea frontend
 */
class FixedBundleWithSpecialPriceCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\Bootstrap */
    protected $objectManager;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    const CUSTOM_OPTION_PRICE_TYPE_FIXED = 'fixed';

    const CUSTOM_OPTION_PRICE_TYPE_PERCENT = 'percent';

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
        'price' => null,        // need to set this
        'price_type' => LinkInterface::PRICE_TYPE_FIXED,
        'can_change_quantity' => 0
    ];

    protected $fixtureForProductCustomOption = [
        'option_id' => null,
        'previous_group' => 'text',
        'title' => 'Test Field',
        'type' => 'field',
        'is_require' => 1,
        'sort_order' => 0,
        'price' => 100,
        'price_type' => 'fixed',
        'sku' => '1-text',
        'max_characters' => 100,
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
    public function testPriceForFixedBundle(array $strategyModifiers, array $expectedResults)
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
            '
                Testing price for fixed bundle product 
                without any discounts, sub items and options
            ' => [
                'strategy' => $this->getEmptyProductStrategy(),
                'expectedResults' => [
                    // just product price
                    'minimalPrice' => 110,

                    // just product price
                    'maximalPrice' => 110
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price and without any sub items and options
            ' => [
                'strategy' => $this->getEmptyProductWithSpecialPriceStrategy(),
                'expectedResults' => [
                    // 110 * 0.5
                    'minimalPrice' => 55,

                    // 110 * 0.5
                    'maximalPrice' => 55
                ]
            ],

            '
                Testing price for fixed bundle product 
                with fixed sub items, fixed options and without any discounts
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 110 + 1 * 20 + 100
                    'minimalPrice' => 230,

                    // 110 + 1 * 20 + 100
                    'maximalPrice' => 230
                ]
            ],

            '
                Testing price for fixed bundle product 
                with percent sub items, percent options and without any discounts
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 110 + 110 * 0.2 + 110 * 1
                    'minimalPrice' => 242,

                    // 110 + 110 * 0.2 + 110 * 1
                    'maximalPrice' => 242
                ]
            ],

            '
                Testing price for fixed bundle product 
                with fixed sub items, percent options and without any discounts
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 110 + 1 * 20 + 110 * 1
                   'minimalPrice' => 240,

                    // 110 + 1 * 20 + 110 * 1
                   'maximalPrice' => 240
                ]
            ],

            '
                Testing price for fixed bundle product 
                with percent sub items, fixed options and without any discounts
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 110 + 110 * 0.2 + 100
                   'minimalPrice' => 232,

                    // 110 + 110 * 0.2 + 100
                   'maximalPrice' => 232
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and fixed options Configuration #1
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 20) + 100
                    'minimalPrice' => 165,

                    // 0.5 * (110 + 1 * 20) + 100
                    'maximalPrice' => 165
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and percent options Configuration #1
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 110 * 0.2 + 110 * 1)
                    'minimalPrice' => 121,

                    // 0.5 * (110 + 110 * 0.2 + 110 * 1)
                    'maximalPrice' => 121
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and percent options Configuration #1
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 20 + 110 * 1)
                    'minimalPrice' => 120,

                    // 0.5 * (110 + 1 * 20 + 110 * 1)
                    'maximalPrice' => 120
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and fixed options Configuration #1
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration1(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 110 * 0.2) + 100
                    'minimalPrice' => 166,

                    // 0.5 * (110 + 110 * 0.2) + 100
                    'maximalPrice' => 166
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and fixed options Configuration #2
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * 110 + 100
                    'minimalPrice' => 155,

                    // 0.5 * (110 + 2 * 20) + 100
                    'maximalPrice' => 175
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and percent options Configuration #2
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 110 * 1)
                    'minimalPrice' => 110,

                    // 0.5 * (110 + 2 * 110 * 0.2 + 1 * 110)
                    'maximalPrice' => 132
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and percent options Configuration #2
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 110)
                    'minimalPrice' => 110,

                    // 0.5 * (110 + 2 * 20 + 1 * 110)
                    'maximalPrice' => 130
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and fixed options Configuration #2
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration2(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * 110 + 100
                    'minimalPrice' => 155,

                    // 0.5 * (110 + 2 * 0.2 * 110) + 100
                    'maximalPrice' => 177
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and fixed options Configuration #3
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 3 * 10) + 100
                    'minimalPrice' => 170,

                    // 0.5 * (110 + 3 * 10 + 1 * 40) + 100
                    'maximalPrice' => 190
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and percent options Configuration #3
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 3 * 110 * 0.1 + 110 * 1)
                    'minimalPrice' => 126.5,

                    // 0.5 * (110 + 3 * 110 * 0.1 + 1 * 110 * 0.4 + 110 * 1)
                    'maximalPrice' => 148.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and percent options Configuration #3
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 3 * 10 + 1 * 110)
                    'minimalPrice' => 125,

                    // 0.5 * (110 + 3 * 10 + 1 * 40 + 1 * 110)
                    'maximalPrice' => 145
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and fixed options Configuration #3
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration3(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 3 * 110 * 0.1) + 100
                    'minimalPrice' => 171.5,

                    // 0.5 * (110 + 3 * 110 * 0.1 + 1 * 110 * 0.4) + 100
                    'maximalPrice' => 193.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and fixed options Configuration #4
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 40) + 100
                    'minimalPrice' => 175,

                    // 0.5 * (110 + 1 * 40 + 3 * 15) + 100
                    'maximalPrice' => 197.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and percent options Configuration #4
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 110 * 0.4 + 1 * 110)
                    'minimalPrice' => 132,

                    // 0.5 * (110 + 1 * 110 * 0.4 + 3 * 110 * 0.15 + 110 * 1)
                    'maximalPrice' => 156.75
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and percent options Configuration #4
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 40 + 1 * 110)
                    'minimalPrice' => 130,

                    // 0.5 * (110 + 1 * 40 + 3 * 15 + 1 * 110)
                    'maximalPrice' => 152.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and fixed options Configuration #4
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration4(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 110 * 0.4) + 100
                    'minimalPrice' => 177,

                    // 0.5 * (110 + 1 * 110 * 0.4 + 3 * 110 * 0.15) + 100
                    'maximalPrice' => 201.75
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and fixed options Configuration #5
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 40) + 100
                    'minimalPrice' => 175,

                    // 0.5 * (110 + 3 * 15) + 100
                    'maximalPrice' => 177.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and percent options Configuration #5
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 110 * 0.4 + 1 * 110)
                    'minimalPrice' => 132,

                    // 0.5 * (110 + 3 * 110 * 0.15 + 1 * 110)
                    'maximalPrice' => 134.75
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and percent options Configuration #5
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 40 + 110 * 1)
                    'minimalPrice' => 130,

                    // 0.5 * (110 + 3 * 15 + 110 * 1)
                    'maximalPrice' => 132.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and fixed options Configuration #5
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration5(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 110 * 0.4) + 100
                    'minimalPrice' => 177,

                    // 0.5 * (110 + 3 * 110 * 0.15) + 100
                    'maximalPrice' => 179.75
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and fixed options Configuration #6
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 40 + 1 * 20) + 100
                    'minimalPrice' => 185,

                    // 0.5 * (110 + 3 * 15 + 1 * 20 + 3 * 10) + 100
                    'maximalPrice' => 202.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and percent options Configuration #6
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 110 * 0.4 + 1 * 110 * 0.2 + 110 * 1)
                    'minimalPrice' => 143,

                    // 0.5 * (110 + 3 * 110 * 0.15 + 1 * 110 * 0.2 + 3 * 110 * 0.1 + 110 * 1)
                    'maximalPrice' => 162.25
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, fixed sub items and percent options Configuration #6
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_FIXED,
                    self::CUSTOM_OPTION_PRICE_TYPE_PERCENT
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 40 + 1 * 20 + 1 * 110)
                    'minimalPrice' => 140,

                    // 0.5 * (110 + 3 * 15 + 1 * 20 + 3 * 10 + 1 * 110)
                    'maximalPrice' => 157.5
                ]
            ],

            '
                Testing price for fixed bundle product 
                with special price, percent sub items and fixed options Configuration #6
            ' => [
                'strategy' => $this->getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration6(
                    LinkInterface::PRICE_TYPE_PERCENT,
                    self::CUSTOM_OPTION_PRICE_TYPE_FIXED
                ),
                'expectedResults' => [
                    // 0.5 * (110 + 1 * 110 * 0.4 + 1 * 110 * 0.2) + 100
                    'minimalPrice' => 188,

                    // 0.5 * (110 + 3 * 110 * 0.15 + 1 * 110 * 0.2 + 3 * 110 * 0.1) + 100
                    'maximalPrice' => 207.25
                ]
            ],
        ];
    }

    public function getEmptyProductStrategy()
    {
        return [];
    }

    public function getEmptyProductWithSpecialPriceStrategy()
    {
        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
        ];
    }

    public function getProductWithSubItemsAndOptionsStrategy($selectionsPriceType, $customOptionsPriceType)
    {
        $optionsData = [
            [
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                        'price' => 20,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ],
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType
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

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration1(
        $selectionsPriceType,
        $customOptionsPriceType
    ) {
        $optionsData = [
            [
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                        'price' => 20,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType
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
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration2(
        $selectionsPriceType,
        $customOptionsPriceType
    ) {
        $optionsData = [
            [
                'required' => false,
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                        'price' => 20,
                        'qty' => 2,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType
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
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration3(
        $selectionsPriceType,
        $customOptionsPriceType
    ) {
        $optionsData = [
            [
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
                        'price' => 10,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType
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
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration4(
        $selectionsPriceType,
        $customOptionsPriceType
    ) {
        $optionsData = [
            [
                'type' => 'multi',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
                        'price' => 15,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType
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
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration5(
        $selectionsPriceType,
        $customOptionsPriceType
    ) {
        $optionsData = [
            [
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
                        'price' => 15,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType
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
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductWithSpecialPriceSubItemsAndOptionsStrategyConfiguration6(
        $selectionsPriceType,
        $customOptionsPriceType
    ) {
        $optionsData = [
            [
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 1,
                        'price' => 40,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 1,
                        'price' => 15,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ],
            [
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'option_id' => 2,
                        'price' => 20,
                        'price_type' => $selectionsPriceType
                    ],
                    [
                        'sku' => 'simple2',
                        'option_id' => 2,
                        'price' => 10,
                        'qty' => 3,
                        'price_type' => $selectionsPriceType
                    ],
                ]
            ]
        ];

        $customOptionsData = [
            [
                'price_type' => $customOptionsPriceType
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
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
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

    protected function getFixtureForProductCustomOption(array $data = [])
    {
        $fixture = $this->fixtureForProductCustomOption;

        // make title and sku different for each call
        $fixture['title'] .= ' ' . microtime(true);
        $fixture['sku'] .= ' ' . microtime(true);

        return array_merge($fixture, $data);
    }

    protected function addSpecialPrice(\Magento\Catalog\Model\Product $bundleProduct, $discount)
    {
        $bundleProduct->setSpecialPrice($discount);

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

    protected function addCustomOption(\Magento\Catalog\Model\Product $bundleProduct, array $optionsData)
    {
        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
        $customOptionFactory = $this->objectManager
            ->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);

        $options = [];
        foreach ($optionsData as $optionData) {
            $customOption = $customOptionFactory->create(
                [
                    'data' => $this->getFixtureForProductCustomOption($optionData)
                ]
            );
            $customOption->setProductSku($bundleProduct->getSku());
            $customOption->setOptionId(null);

            $options[] = $customOption;
        }

        $bundleProduct->setOptions($options);
        $bundleProduct->setCanSaveCustomOptions(true);

        return $bundleProduct;
    }
}
