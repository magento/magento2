<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;

/**
 * Product attribute data for attribute with text editor input type.
 */
class TextEditor extends AbstractBaseAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        static::$defaultAttributePostData['used_for_sort_by'] = '0';
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeData(): array
    {
        return array_replace_recursive(
            parent::getAttributeData(),
            [
                "{static::getFrontendInput()}_with_default_value" => [
                    [
                        'default_value_text' => '',
                        'default_value_textarea' => 'Default attribute value',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public static function getAttributeDataWithCheckArray(): array
    {
        return array_replace_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{static::getFrontendInput()}_with_required_fields" => [
                    1 => [
                        'frontend_input' => 'textarea',
                    ],
                ],
                "{static::getFrontendInput()}_with_store_view_scope" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_with_global_scope" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_with_website_scope" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_with_attribute_code" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_with_default_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_without_default_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_with_unique_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_without_unique_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_with_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_without_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_with_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{static::getFrontendInput()}_without_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
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
                        'attribute_code' => 'text_editor_attribute',
                    ],
                ],
                "{$frontendInput}_change_frontend_input" => [
                    'postData' => [
                        'frontend_input' => 'textarea',
                    ],
                    'expectedData' => [
                        'frontend_input' => 'textarea',
                        'is_wysiwyg_enabled' => '0'
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected static function getFrontendInput(): string
    {
        return 'texteditor';
    }

    /**
     * @inheritdoc
     */
    protected static function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Text Editor Attribute Update',
            ],
            'frontend_input' => 'texteditor',
            'is_required' => '1',
            'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'default_value_textarea' => 'Text Editor Attribute Default',
            'is_unique' => '1',
            'is_used_in_grid' => '1',
            'is_visible_in_grid' => '1',
            'is_filterable_in_grid' => '1',
            'is_searchable' => '1',
            'search_weight' => '2',
            'is_visible_in_advanced_search' => '1',
            'is_comparable' => '1',
            'is_used_for_promo_rules' => '1',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '1',
            'used_in_product_listing' => '1',
            'used_for_sort_by' => '1',
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function getUpdateExpectedData(): array
    {
        $updatePostData = static::getUpdatePostData();
        unset($updatePostData['default_value_textarea']);
        return array_merge(
            $updatePostData,
            [
                'frontend_label' => 'Text Editor Attribute Update',
                'frontend_input' => 'textarea',
                'attribute_code' => 'text_editor_attribute',
                'default_value' => 'Text Editor Attribute Default',
                'frontend_class' => null,
                'is_filterable' => '0',
                'is_filterable_in_search' => '0',
                'position' => '0',
                'is_user_defined' => '1',
                'backend_type' => 'text',
                'is_wysiwyg_enabled' => '1',
            ]
        );
    }
}
