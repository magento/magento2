<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Swatches\Model\Attribute\DataProvider;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Swatches\Model\Swatch;
use Magento\Store\Model\Store;

/**
 * Product attribute data for attribute with input type visual swatch.
 */
class TextSwatch extends AbstractSwatchAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData['swatch_input_type'] = 'text';
    }

    /**
     * @inheritdoc
     */
    public function getAttributeDataWithCheckArray(): array
    {
        return array_replace_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{$this->getFrontendInput()}_with_required_fields" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_store_view_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_global_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_website_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_attribute_code" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_unique_value" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_without_unique_value" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateProvider(): array
    {
        $frontendInput = $this->getFrontendInput();
        return array_replace_recursive(
            parent::getUpdateProvider(),
            [
                "{$frontendInput}_other_attribute_code" => [
                    'post_data' => [
                        'attribute_code' => 'text_attribute_update',
                    ],
                    'expected_data' => [
                        'attribute_code' => 'text_swatch_attribute',
                    ],
                ],
                "{$frontendInput}_change_frontend_input_swatch_visual" => [
                    'post_data' => [
                        'frontend_input' => Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
                        'update_product_preview_image' => '1',
                        'use_product_image_for_swatch' => '1',
                    ],
                    'expected_data' => [
                        'frontend_input' => 'select',
                        'swatch_input_type' => Swatch::SWATCH_INPUT_TYPE_VISUAL,
                        'update_product_preview_image' => '1',
                        'use_product_image_for_swatch' => '1',
                    ],
                ],
                "{$frontendInput}_change_frontend_input_dropdown" => [
                    'post_data' => [
                        'frontend_input' => 'select',
                    ],
                    'expected_data' => [
                        'frontend_input' => 'select',
                        'swatch_input_type' => null,
                        'update_product_preview_image' => null,
                        'use_product_image_for_swatch' => null,
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOptionsProvider(): array
    {
        $frontendInput = $this->getFrontendInput();
        return array_replace_recursive(
            parent::getUpdateOptionsProvider(),
            [
                "{$frontendInput}_update_options" => [
                    'post_data' => [
                        'options_array' => [
                            'option_1' => [
                                'order' => '4',
                                'swatch' => [
                                    Store::DEFAULT_STORE_ID => 'Swatch 1 Admin',
                                    'default' => 'Swatch 1 Store 1',
                                    'fixture_second_store' => 'Swatch 1 Store 2',
                                    'fixture_third_store' => 'Swatch 1 Store 3',
                                ],
                            ],
                            'option_2' => [
                                'order' => '5',
                                'swatch' => [
                                    Store::DEFAULT_STORE_ID => 'Swatch 2 Admin',
                                    'default' => 'Swatch 2 Store 1',
                                    'fixture_second_store' => 'Swatch 2 Store 2',
                                    'fixture_third_store' => 'Swatch 2 Store 3',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function getOptionsDataArr(): array
    {
        return [
            [
                'optiontext' => [
                    'order' => [
                        'option_0' => '1',
                    ],
                    'value' => [
                        'option_0' => [
                            0 => 'Admin value description 1',
                            1 => 'Default store view value description 1',
                        ],
                    ],
                    'delete' => [
                        'option_0' => '',
                    ],
                ],
                'defaulttext' => [
                    0 => 'option_0',
                ],
                'swatchtext' => [
                    'value' => [
                        'option_0' => [
                            0 => 'Admin value 1',
                            1 => 'Default store view value 1',
                        ],
                    ],
                ],
            ],
            [
                'optiontext' => [
                    'order' => [
                        'option_1' => '2',
                    ],
                    'value' => [
                        'option_1' => [
                            0 => 'Admin value description 2',
                            1 => 'Default store view value description 2',
                        ],
                    ],
                    'delete' => [
                        'option_1' => '',
                    ],
                ],
                'swatchtext' => [
                    'value' => [
                        'option_1' => [
                            0 => 'Admin value 2',
                            1 => 'Default store view value 2',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getFrontendInput(): string
    {
        return Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT;
    }

    /**
     * @inheritdoc
     */
    protected function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Text swatch attribute Update',
            ],
            'frontend_input' => Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT,
            'is_required' => '1',
            'update_product_preview_image' => '1',
            'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'is_unique' => '1',
            'is_used_in_grid' => '1',
            'is_visible_in_grid' => '1',
            'is_filterable_in_grid' => '1',
            'is_searchable' => '1',
            'search_weight' => '2',
            'is_visible_in_advanced_search' => '1',
            'is_comparable' => '1',
            'is_filterable' => '2',
            'is_filterable_in_search' => '1',
            'position' => '2',
            'is_used_for_promo_rules' => '1',
            'is_visible_on_front' => '1',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '1',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getUpdateExpectedData(): array
    {
        $updatePostData = $this->getUpdatePostData();
        return array_merge(
            $updatePostData,
            [
                'frontend_label' => 'Text swatch attribute Update',
                'frontend_input' => 'select',
                'attribute_code' => 'text_swatch_attribute',
                'default_value' => null,
                'frontend_class' => null,
                'is_html_allowed_on_front' => '1',
                'is_user_defined' => '1',
                'backend_type' => 'int',
            ]
        );
    }
}
