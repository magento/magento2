<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class CartPriceRulesFixture
 */
class CartPriceRulesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 70;

    /**
     * @var float
     */
    protected $cartPriceRulesCount = 0;

    /**
     * @var float
     */
    protected $cartPriceRulesProductsFloor = 3;

    /**
     * @var bool
     */
    protected $cartRulesAdvancedType = false;

    /**
     * @var float
     */
    protected $cartRulesAdvancedRatio = 0.70;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $this->fixtureModel->resetObjectManager();
        $this->cartPriceRulesCount = $this->fixtureModel->getValue('cart_price_rules', 0);
        if (!$this->cartPriceRulesCount) {
            return;
        }

        $this->cartRulesAdvancedType = $this->fixtureModel->getValue('cart_price_rules_advanced_type', false);
        $this->cartPriceRulesProductsFloor = $this->fixtureModel->getValue(
            'cart_price_rules_floor',
            3
        );

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create('Magento\Store\Model\StoreManager');
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->get('Magento\Catalog\Model\Category');
        /** @var $model  \Magento\SalesRule\Model\Rule*/
        $model = $this->fixtureModel->getObjectManager()->get('Magento\SalesRule\Model\Rule');

        //Get all websites
        $categoriesArray = [];
        $websites = $storeManager->getWebsites();
        foreach ($websites as $website) {
            //Get all groups
            $websiteGroups = $website->getGroups();
            foreach ($websiteGroups as $websiteGroup) {
                $websiteGroupRootCategory = $websiteGroup->getRootCategoryId();
                $category->load($websiteGroupRootCategory);
                $categoryResource = $category->getResource();
                //Get all categories
                $resultsCategories = $categoryResource->getAllChildren($category);
                foreach ($resultsCategories as $resultsCategory) {
                    $category->load($resultsCategory);
                    $structure = explode('/', $category->getPath());
                    if (count($structure) > 2) {
                        $categoriesArray[] = [$category->getId(), $website->getId()];
                    }
                }
            }
        }
        asort($categoriesArray);
        $categoriesArray = array_values($categoriesArray);

        if ($this->cartRulesAdvancedType == false) {
            $this->generateConditions($model, $categoriesArray);
        } else {
            $this->generateProductAttributeCombination($model, $categoriesArray);
            $this->generateProductSubSelection($model, $categoriesArray);
        }
    }

    public function generateConditions($model, $categoriesArray)
    {
        $idField = $model->getIdFieldName();

        for ($i = 0; $i < $this->cartPriceRulesCount; $i++) {
            $ruleName = sprintf('Cart Price Rule %1$d', $i);
            $data = [
                $idField                => null,
                'product_ids'           => '',
                'name'                  => $ruleName,
                'description'           => '',
                'is_active'             => '1',
                'website_ids'           => $categoriesArray[$i % count($categoriesArray)][1],
                'customer_group_ids'    => [
                    0 => '0',
                    1 => '1',
                    2 => '2',
                    3 => '3',
                ],
                'coupon_type'           => '1',
                'coupon_code'           => '',
                'uses_per_customer'     => '',
                'from_date'             => '',
                'to_date'               => '',
                'sort_order'            => '',
                'is_rss'                => '1',
                'rule'                  => [
                    'conditions' => [
                        1 => [
                            'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Combine',
                            'aggregator' => 'all',
                            'value' => '1',
                            'new_child' => '',
                        ],
                        '1--1' => [
                            'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                            'attribute' => 'total_qty',
                            'operator' => '>=',
                            'value' => $this->cartPriceRulesProductsFloor + $i,
                        ],
                        '1--2' => [
                            'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Found',
                            'value' => '1',
                            'aggregator' => 'all',
                            'new_child' => '',
                        ],
                        '1--2--1' => [
                            'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                            'attribute' => 'category_ids',
                            'operator' => '==',
                            'value' => $categoriesArray[$i % count($categoriesArray)][0],
                        ],
                    ],
                    'actions' => [
                        1 => [
                            'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine',
                            'aggregator' => 'all',
                            'value' => '1',
                            'new_child' => '',
                        ],
                    ],
                ],
                'simple_action'             => 'by_percent',
                'discount_amount'           => '10',
                'discount_qty'              => '0',
                'discount_step'             => '',
                'apply_to_shipping'         => '0',
                'simple_free_shipping'      => '0',
                'stop_rules_processing'     => '0',
                'reward_points_delta'       => '',
                'store_labels'              => [
                    0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => '',
                    6 => '',
                    7 => '',
                    8 => '',
                    9 => '',
                    10 => '',
                    11 => '',
                ],
                'page'                      => '1',
                'limit'                     => '20',
                'in_banners'                => '',
                'banner_id'                 => [
                    'from'  => '',
                    'to'    => '',
                ],
                'banner_name'               => '',
                'visible_in'                => '',
                'banner_is_enabled'         => '',
                'related_banners'           => [],
            ];
            if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                && isset($data['discount_amount'])
            ) {
                $data['discount_amount'] = min(100, $data['discount_amount']);
            }
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
            }
            unset($data['rule']);

            $model->loadPost($data);
            $useAutoGeneration = (int)!empty($data['use_auto_generation']);
            $model->setUseAutoGeneration($useAutoGeneration);
            $model->save();
        }
    }

    public function generateProductAttributeCombinationCondition($ruleId, $categoriesArray)
    {
        if ($ruleId % 5 == 0) {
            // Price in cart
            $firstCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'quote_item_price',
                'operator'  => '>=',
                'value'     => $ruleId * 20,
            ];

            // Shipping Country
            $secondCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'country_id',
                'operator'  => '==',
                'value'     => 'US',
            ];
        } elseif ($ruleId % 5 == 1) {
            // Quantity in cart
            $firstCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'quote_item_qty',
                'operator'  => '>=',
                'value'     => $ruleId * 2,
            ];

            // Shipping Method
            $secondCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'shipping_method',
                'operator'  => '==',
                'value'     => 'flatrate_flatrate',
            ];
        } elseif ($ruleId % 5 == 2) {
            // Rowtotal in cart
            $firstCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'quote_item_row_total',
                'operator'  => '>=',
                'value'     => $ruleId,
            ];

            // Payment Method
            $secondCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'payment_method',
                'operator'  => '==',
                'value'     => 'checkmo',
            ];
        } elseif ($ruleId % 5 == 3) {
            // Attribute set
            $firstCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'attribute_set_id',
                'operator'  => '==',
                'value'     => 1,
            ];

            // Total Weight
            $secondCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'weight',
                'operator'  => '>=',
                'value'     => $ruleId,
            ];
        } else {
            // Category
            $firstCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'category_ids',
                'operator'  => '==',
                'value'     => $categoriesArray[$ruleId % count($categoriesArray)][0],
            ];

            // Total Items Quantity
            $secondCondition = [
                'type'      => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Address',
                'attribute' => 'total_qty',
                'operator'  => '>=',
                'value'     => $ruleId,
            ];
        }

        return [
            'conditions' => [
                1 => [
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1'=> [
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Found',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1--1' => $firstCondition,
                '1--2' => $secondCondition
            ],
            'actions' => [
                1 => [
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
            ]
        ];
    }

    // Creates a rule with one product attribute condtion and one cart condition 
    public function generateProductAttributeCombination($model, $categoriesArray)
    {
        $idField = $model->getIdFieldName();

        $combinationRulesCount = (int)($this->cartPriceRulesCount * $this->cartRulesAdvancedRatio);
        var_dump($combinationRulesCount);
        for ($i = 0; $i < $combinationRulesCount; $i++) {
            $ruleName = sprintf('Cart Price Combination Rule %1$d', $i);
            $data = [
                $idField                => null,
                'product_ids'           => '',
                'name'                  => $ruleName,
                'description'           => '',
                'is_active'             => '1',
                'website_ids'           => $categoriesArray[$i % count($categoriesArray)][1],
                'customer_group_ids'    => [
                    0 => '0',
                    1 => '1',
                    2 => '2',
                    3 => '3',
                ],
                'coupon_type'           => '1',
                'coupon_code'           => '',
                'uses_per_customer'     => '',
                'from_date'             => '',
                'to_date'               => '',
                'sort_order'            => '',
                'is_rss'                => '1',
                'rule'                  => $this->generateProductAttributeCombinationCondition($i, $categoriesArray),
                'simple_action'             => 'by_fixed',
                'discount_amount'           => '1',
                'discount_qty'              => '0',
                'discount_step'             => '',
                'apply_to_shipping'         => '0',
                'simple_free_shipping'      => '0',
                'stop_rules_processing'     => '0',
                'reward_points_delta'       => '',
                'store_labels'              => [
                    0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => '',
                    6 => '',
                    7 => '',
                    8 => '',
                    9 => '',
                    10 => '',
                    11 => '',
                ],
                'page'                      => '1',
                'limit'                     => '20',
                'in_banners'                => '',
                'banner_id'                 => [
                    'from'  => '',
                    'to'    => '',
                ],
                'banner_name'               => '',
                'visible_in'                => '',
                'banner_is_enabled'         => '',
                'related_banners'           => [],
            ];
            if (isset($data['simple_action']) && $data['simple_action'] == 'by_fixed'
                && isset($data['discount_amount'])
            ) {
                $data['discount_amount'] = min(1, $data['discount_amount']);
            }
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
            }
            unset($data['rule']);

            $model->loadPost($data);
            $useAutoGeneration = (int)!empty($data['use_auto_generation']);
            $model->setUseAutoGeneration($useAutoGeneration);
            $model->save();
        }
    }

    public function generateProductSubSelectionCondition($ruleId, $categoriesArray)
    {
        if ($ruleId % 2 == 0) {
            // Total Qty
            $firstCondition = [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Subselect',
                'attribute' => 'qty',
                'operator' => '>=',
                'value' => $ruleId,
            ];
        } else {
            // Total Amount
            $firstCondition = [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\Subselect',
                'attribute' => 'base_row_total',
                'operator' => '>=',
                'value' => $ruleId * 10,
            ];
        }

        if ($ruleId % 5 == 0) {
            // Price in cart
            $secondCondition = [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'quote_item_price',
                'operator' => '>=',
                'value' => $ruleId * 20,
            ];
        } else if ($ruleId % 5 == 1) {
            // Quantity in cart
            $secondCondition = [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'quote_item_qty',
                'operator' => '>=',
                'value' => $ruleId * 2,
            ];
        } else if ($ruleId % 5 == 2) {
            // Rowtotal in cart
            $secondCondition = [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'quote_item_row_total',
                'operator' => '>=',
                'value' => $ruleId,
            ];
        } else if ($ruleId % 5 == 3) {
            // Attribute set
            $secondCondition = [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'attribute_set_id',
                'operator' => '==',
                'value' => 1,
            ];
        } else {
            // Category
            $secondCondition = [
                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                'attribute' => 'category_ids',
                'operator' => '==',
                'value' => $categoriesArray[$ruleId % count($categoriesArray)][0]
            ];
        }

        return [
            'conditions' => [
                1 => [
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => $firstCondition,
                '1--1--1' => $secondCondition
            ],
            'actions' => [
                1 => [
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
            ]
        ];
    }

    // Creates a rule with one product subselection condition
    public function generateProductSubSelection($model, $categoriesArray)
    {
        $idField = $model->getIdFieldName();

        $subselectionCount = (int)($this->cartPriceRulesCount * (1 - $this->cartRulesAdvancedRatio));
        var_dump($subselectionCount);
        for ($i = 0; $i < $subselectionCount; $i++) {
            $ruleName = sprintf('Cart Price SubSelection Rule %1$d', $i);
            $data = [
                $idField                => null,
                'product_ids'           => '',
                'name'                  => $ruleName,
                'description'           => '',
                'is_active'             => '1',
                'website_ids'           => $categoriesArray[$i % count($categoriesArray)][1],
                'customer_group_ids'    => [
                    0 => '0',
                    1 => '1',
                    2 => '2',
                    3 => '3',
                ],
                'coupon_type'           => '1',
                'coupon_code'           => '',
                'uses_per_customer'     => '',
                'from_date'             => '',
                'to_date'               => '',
                'sort_order'            => '',
                'is_rss'                => '1',
                'rule'                  => $this->generateProductSubSelectionCondition($i, $categoriesArray),
                'simple_action'             => 'by_fixed',
                'discount_amount'           => '1',
                'discount_qty'              => '0',
                'discount_step'             => '',
                'apply_to_shipping'         => '0',
                'simple_free_shipping'      => '0',
                'stop_rules_processing'     => '0',
                'reward_points_delta'       => '',
                'store_labels'              => [
                    0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => '',
                    6 => '',
                    7 => '',
                    8 => '',
                    9 => '',
                    10 => '',
                    11 => '',
                ],
                'page'                      => '1',
                'limit'                     => '20',
                'in_banners'                => '',
                'banner_id'                 => [
                    'from'  => '',
                    'to'    => '',
                ],
                'banner_name'               => '',
                'visible_in'                => '',
                'banner_is_enabled'         => '',
                'related_banners'           => [],
            ];
            if (isset($data['simple_action']) && $data['simple_action'] == 'by_fixed'
                && isset($data['discount_amount'])
            ) {
                $data['discount_amount'] = min(1, $data['discount_amount']);
            }
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
            }
            unset($data['rule']);

            $model->loadPost($data);
            $useAutoGeneration = (int)!empty($data['use_auto_generation']);
            $model->setUseAutoGeneration($useAutoGeneration);
            $model->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating Cart Price Rules';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'cart_price_rules' => 'Cart Price Rules'
        ];
    }
}
