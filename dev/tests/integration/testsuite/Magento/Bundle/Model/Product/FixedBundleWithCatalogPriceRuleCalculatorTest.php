<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

use \Magento\Bundle\Api\Data\LinkInterface;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product_with_catalog_rule.php
 * @magentoAppArea frontend
 */
class FixedBundleWithCatalogPriceRuleCalculatorTest extends \PHPUnit_Framework_TestCase
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
                with catalog price rule and without sub items and options
            ' => [
                'strategy' => $this->getEmptyProductStrategy(),
                'expectedResults' => [
                    // 110 * 0.9
                    'minimalPrice' => 99,

                    // 110 * 0.9
                    'maximalPrice' => 99
                ]
            ],

            '
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options
            ' => [
                'strategy' => $this->getProductWithSubItemsAndOptionsStrategy(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #1
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration1(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #2
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration2(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #3
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration3(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #4
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration4(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #5
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration5(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and fixed options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and percent options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
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
                Testing price for fixed bundle product
                with catalog price rule, fixed sub items and percent options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
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
                Testing price for fixed bundle product
                with catalog price rule, percent sub items and fixed options Configuration #6
            ' => [
                'strategy' => $this->getProductSubItemsAndOptionsStrategyConfiguration6(
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

    public function getEmptyProductStrategy()
    {
        return [];
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

    public function getProductSubItemsAndOptionsStrategyConfiguration1($selectionsPriceType, $customOptionsPriceType)
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
            ]
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

    public function getProductSubItemsAndOptionsStrategyConfiguration2($selectionsPriceType, $customOptionsPriceType)
    {
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
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductSubItemsAndOptionsStrategyConfiguration3($selectionsPriceType, $customOptionsPriceType)
    {
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
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductSubItemsAndOptionsStrategyConfiguration4($selectionsPriceType, $customOptionsPriceType)
    {
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
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductSubItemsAndOptionsStrategyConfiguration5($selectionsPriceType, $customOptionsPriceType)
    {
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
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
            [
                'modifierName' => 'addCustomOption',
                'data' => [$customOptionsData]
            ],
        ];
    }

    public function getProductSubItemsAndOptionsStrategyConfiguration6($selectionsPriceType, $customOptionsPriceType)
    {
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
