<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Repository;

use Magento\Catalog\Test\Fixture;

/**
 * Class Product Repository
 *
 * Data for create simple product
 */
class SimpleProduct extends Product
{
    const ADVANCED_INVENTORY = 'simple_advanced_inventory';

    const ADVANCED_PRICING = 'simple_advanced_pricing';

    const SIMPLE_WITH_MAP = 'simple_with_map';

    const SIMPLE_OUT_OF_STOCK = 'simple_out_of_stock';

    const BASE = 'simple';

    const CUSTOM_OPTIONS = 'simple_custom_options';

    const NEW_CATEGORY = 'simple_with_new_category';

    /**
     * {inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        parent::__construct($defaultConfig, $defaultData);
        $this->_data[self::ADVANCED_INVENTORY] = $this->getSimpleAdvancedInventory();
        $this->_data[self::NEW_CATEGORY] = [
            'config' => $defaultConfig,
            'data' => $this->buildSimpleWithNewCategoryData($defaultData),
        ];
        $this->_data[self::ADVANCED_PRICING] = $this->getSimpleAdvancedPricing();
        $this->_data[self::CUSTOM_OPTIONS] = $this->getSimpleCustomOption();
        $this->_data[self::SIMPLE_WITH_MAP] = $this->getSimpleAppliedMap($defaultData);
        $this->_data[self::SIMPLE_OUT_OF_STOCK] = $this->_getSimpleOutOfStock();
    }

    /**
     * Build data for simple product with new category
     *
     * @param array $defaultData
     * @return array
     */
    protected function buildSimpleWithNewCategoryData($defaultData)
    {
        return [
            'category_new' => [
                'category_name' => ['value' => 'New category %isolation%'],
                'parent_category' => ['value' => 'Default'],
            ],
            'category_name' => '%category::getCategoryName%',
            'category_id' => '%category::getCategoryId%',
            'fields' => array_intersect_key(
                $defaultData['fields'],
                array_flip(['name', 'sku', 'price', 'weight', 'product_website_1'])
            )
        ];
    }

    /**
     * Get simple product with advanced inventory
     *
     * @return array
     */
    protected function getSimpleAdvancedInventory()
    {
        $inventory = [
            'data' => [
                'fields' => [
                    'inventory_manage_stock' => ['value' => 'Yes', 'input_value' => '1'],
                    'inventory_qty' => ['value' => 1, 'group' => Fixture\Product::GROUP_PRODUCT_INVENTORY],
                ],
            ],
        ];
        $product = array_replace_recursive($this->_data['simple'], $inventory);
        unset($product['data']['fields']['qty']);

        return $product;
    }

    /**
     * Get simple product with advanced pricing
     *
     * @return array
     */
    protected function getSimpleAdvancedPricing()
    {
        $pricing = [
            'data' => [
                'fields' => [
                    'special_price' => ['value' => '9', 'group' => Fixture\Product::GROUP_PRODUCT_PRICING],
                ],
            ],
        ];
        $product = array_replace_recursive($this->_data['simple'], $pricing);

        return $product;
    }

    /**
     * @param string $productType
     * @return array
     */
    protected function resetRequiredFields($productType)
    {
        return array_replace_recursive(
            parent::resetRequiredFields($productType),
            [
                'data' => [
                    'fields' => [
                        'price' => ['value' => '1.99', 'group' => Fixture\Product::GROUP_PRODUCT_DETAILS],
                    ],
                ]
            ]
        );
    }

    /**
     * @return array
     */
    protected function getSimpleCustomOption()
    {
        return array_merge_recursive(
            $this->_data['simple'],
            [
                'data' => [
                    'fields' => [
                        'custom_options' => [
                            'value' => [
                                [
                                    'title' => 'custom option drop down',
                                    'is_require' => true,
                                    'type' => 'Drop-down',
                                    'options' => [
                                        [
                                            'title' => 'Title Drop - down 1',
                                            'price' => 2.56,
                                            'price_type' => 'Fixed',
                                            'sku' => 'sku_drop_down_row_1',
                                        ],
                                    ],
                                ],
                            ],
                            'group' => Fixture\Product::GROUP_CUSTOM_OPTIONS,
                        ],
                    ],
                ]
            ]
        );
    }

    /**
     * Get simple product with advanced pricing (MAP)
     *
     * @return array
     */
    protected function getSimpleAppliedMap()
    {
        $pricing = [
            'data' => [
                'fields' => [
                    'msrp_display_actual_price_type' => [
                        'value' => 'On Gesture',
                        'input_value' => '1',
                        'group' => Fixture\Product::GROUP_PRODUCT_PRICING,
                        'input' => 'select',
                    ],
                    'msrp' => [
                        'value' => '15',
                        'group' => Fixture\Product::GROUP_PRODUCT_PRICING,
                    ],
                ],
            ],
        ];
        $product = array_replace_recursive($this->_data['simple'], $pricing);

        return $product;
    }
}
