<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Fixture for generating cart price rules
 *
 * Support the following format:
 * <!-- Number of cart price rules -->
 * <cart_price_rules>{int}</cart_price_rules>
 *
 * <!-- Number of conditions per rule -->
 * <cart_price_rules_floor>{int}</cart_price_rules_floor>
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @since 2.0.0
 */
class CartPriceRulesFixture extends Fixture
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $priority = 80;

    /**
     * @var float
     * @since 2.1.0
     */
    protected $cartPriceRulesCount = 0;

    /**
     * @var float
     * @since 2.1.0
     */
    protected $cartPriceRulesProductsFloor = 3;

    /**
     * @var bool
     * @since 2.1.0
     */
    protected $cartRulesAdvancedType = false;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     * @since 2.2.0
     */
    private $ruleFactory;

    /**
     * Constructor
     *
     * @param FixtureModel $fixtureModel
     * @param \Magento\SalesRule\Model\RuleFactory|null $ruleFactory
     * @since 2.2.0
     */
    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory = null
    ) {
        parent::__construct($fixtureModel);
        $this->ruleFactory = $ruleFactory ?: $this->fixtureModel->getObjectManager()
            ->get(\Magento\SalesRule\Model\RuleFactory::class);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     * @since 2.0.0
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
        $storeManager = $this->fixtureModel->getObjectManager()->create(\Magento\Store\Model\StoreManager::class);
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->get(\Magento\Catalog\Model\Category::class);

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
            $this->generateRules($this->ruleFactory, $categoriesArray);
        } else {
            $this->generateAdvancedRules($this->ruleFactory, $categoriesArray);
        }
    }

    /**
     * @param int $ruleId
     * @param array $categoriesArray
     * @return array
     * @since 2.1.0
     */
    public function generateCondition($ruleId, $categoriesArray)
    {
        return [
            'conditions' => [
                1 => [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                    'attribute' => 'total_qty',
                    'operator' => '>=',
                    'value' => $this->cartPriceRulesProductsFloor + $ruleId,
                ],
                '1--2' => [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                    'value' => '1',
                    'aggregator' => 'all',
                    'new_child' => '',
                ],
                '1--2--1' => [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                    'attribute' => 'category_ids',
                    'operator' => '==',
                    'value' => $categoriesArray[$ruleId % count($categoriesArray)][0],
                ],
            ],
            'actions' => [
                1 => [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
            ],
        ];
    }

    /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param array $categoriesArray
     * @return void
     * @since 2.1.0
     */
    public function generateRules($ruleFactory, $categoriesArray)
    {
        for ($i = 0; $i < $this->cartPriceRulesCount; $i++) {
            $ruleName = sprintf('Cart Price Rule %1$d', $i);
            $data = [
                'rule_id'               => null,
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
                'rule'                  => $this->generateCondition($i, $categoriesArray),
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

            $model = $ruleFactory->create();
            $model->loadPost($data);
            $useAutoGeneration = (int)!empty($data['use_auto_generation']);
            $model->setUseAutoGeneration($useAutoGeneration);
            $model->save();
        }
    }

    /**
     * @param int $ruleId
     * @param array $categoriesArray
     * @return array
     * @since 2.1.0
     */
    public function generateAdvancedCondition($ruleId, $categoriesArray)
    {
        // Generate only 200 region rules, the rest are based on category
        if ($ruleId < ($this->cartPriceRulesCount - 200)) {
            // Category
            $firstCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                'attribute' => 'category_ids',
                'operator'  => '==',
                'value'     => $categoriesArray[($ruleId / 4) % count($categoriesArray)][0],
            ];

            $subtotal = [0, 5, 10, 15];
            // Subtotal
            $secondCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'base_subtotal',
                'operator'  => '>=',
                'value'     => $subtotal[$ruleId % 4],
            ];

            return [
                'conditions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1'=> [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1--1' => $firstCondition,
                    '1--2' => $secondCondition
                ],
                'actions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                ]
            ];
        } else {
            // Shipping Region
            $regions = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
                        'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois',
                        'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts',
                        'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
                        'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota',
                        'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
                        'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia',
                        'Wisconsin', 'Wyoming'];
            $firstCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'region',
                'operator'  => '==',
                'value'     => $regions[($ruleId / 4) % 50],
            ];

            $subtotals = [0, 5, 10, 15];
            // Subtotal
            $secondCondition = [
                'type'      => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'base_subtotal',
                'operator'  => '>=',
                'value'     => $subtotals[$ruleId % 4],
            ];
            return [
                'conditions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => $firstCondition,
                    '1--2' => $secondCondition
                ],
                'actions' => [
                    1 => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                ]
            ];
        }
    }

    /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param array $categoriesArray
     * @return void
     * @since 2.1.0
     */
    public function generateAdvancedRules($ruleFactory, $categoriesArray)
    {
        $j = 0;
        for ($i = 0; $i < $this->cartPriceRulesCount; $i++) {
            if ($i < ($this->cartPriceRulesCount - 200)) {
                $ruleName = sprintf('Cart Price Advanced Catalog Rule %1$d', $j);
            } else {
                $ruleName = sprintf('Cart Price Advanced Region Rule %1$d', $j);
            }
            $j++;
            $data = [
                'rule_id'               => null,
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
                'rule'                  => $this->generateAdvancedCondition($i, $categoriesArray),
                'simple_action'             => 'cart_fixed',
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
            if (isset($data['simple_action']) && $data['simple_action'] == 'cart_fixed'
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

            $model = $ruleFactory->create();
            $model->loadPost($data);
            $useAutoGeneration = (int)!empty($data['use_auto_generation']);
            $model->setUseAutoGeneration($useAutoGeneration);
            $model->save();
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getActionTitle()
    {
        return 'Generating cart price rules';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function introduceParamLabels()
    {
        return [
            'cart_price_rules' => 'Cart Price Rules'
        ];
    }
}
