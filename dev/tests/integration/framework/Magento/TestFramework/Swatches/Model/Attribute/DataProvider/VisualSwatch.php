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
class VisualSwatch extends AbstractSwatchAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        static::$defaultAttributePostData['swatch_input_type'] = 'visual';
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeDataWithCheckArray(): array
    {
        return array_replace_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{static::getFrontendInput()}_with_required_fields" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_with_store_view_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_with_global_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_with_website_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_with_attribute_code" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_with_unique_value" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_without_unique_value" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_with_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_without_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_with_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{static::getFrontendInput()}_without_enabled_use_in_filter_options" => [
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
    public static function getUpdateProvider(): array
    {
        $frontendInput = static::getFrontendInput();
        return array_replace_recursive(
            parent::getUpdateProvider(),
            [
                "{$frontendInput}_other_attribute_code" => [
                    'postData' => [
                        'attribute_code' => 'text_attribute_update',
                    ],
                    'expectedData' => [
                        'attribute_code' => 'visual_swatch_attribute',
                    ],
                ],
                "{$frontendInput}_change_frontend_input_swatch_text" => [
                    'postData' => [
                        'frontend_input' => Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT,
                        'update_product_preview_image' => '1',
                    ],
                    'expectedData' => [
                        'frontend_input' => 'select',
                        'swatch_input_type' => Swatch::SWATCH_INPUT_TYPE_TEXT,
                        'update_product_preview_image' => '1',
                        'use_product_image_for_swatch' => 0,
                    ],
                ],
                "{$frontendInput}_change_frontend_input_dropdown" => [
                    'postData' => [
                        'frontend_input' => 'select',
                    ],
                    'expectedData' => [
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
    public static function getUpdateOptionsProvider(): array
    {
        $frontendInput = static::getFrontendInput();
        return array_replace_recursive(
            parent::getUpdateOptionsProvider(),
            [
                "{$frontendInput}_update_options" => [
                    'postData' => [
                        'options_array' => [
                            'option_1' => [
                                'order' => '4',
                                'swatch' => [
                                    Store::DEFAULT_STORE_ID => '#1a1a1a',
                                ],
                            ],
                            'option_2' => [
                                'order' => '5',
                                'swatch' => [
                                    Store::DEFAULT_STORE_ID => '#2b2b2b',
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
    protected static function getOptionsDataArr(): array
    {
        return [
            [
                'optionvisual' => [
                    'order' => [
                        'option_0' => '1',
                    ],
                    'value' => [
                        'option_0' => [
                            0 => 'Admin black test 1',
                            1 => 'Default store view black test 1',
                        ],
                    ],
                    'delete' => [
                        'option_0' => '',
                    ]
                ],
                'swatchvisual' => [
                    'value' => [
                        'option_0' => '#000000',
                    ]
                ]
            ],
            [
                'optionvisual' => [
                    'order' => [
                        'option_1' => '2',
                    ],
                    'value' => [
                        'option_1' => [
                            0 => 'Admin white test 2',
                            1 => 'Default store view white test 2',
                        ],
                    ],
                    'delete' => [
                        'option_1' => '',
                    ],
                ],
                'swatchvisual' => [
                    'value' => [
                        'option_1' => '#ffffff',
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function getFrontendInput(): string
    {
        return Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT;
    }

    /**
     * @inheritdoc
     */
    protected static function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Visual swatch attribute Update',
            ],
            'frontend_input' => Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
            'is_required' => '1',
            'update_product_preview_image' => '1',
            'use_product_image_for_swatch' => '1',
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
    protected static function getUpdateExpectedData(): array
    {
        $updatePostData = static::getUpdatePostData();
        return array_merge(
            $updatePostData,
            [
                'frontend_label' => 'Visual swatch attribute Update',
                'frontend_input' => 'select',
                'attribute_code' => 'visual_swatch_attribute',
                'default_value' => null,
                'frontend_class' => null,
                'is_html_allowed_on_front' => '1',
                'is_user_defined' => '1',
                'backend_type' => 'int',
            ]
        );
    }
}
