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
 * Product attribute data for attribute with input type text.
 */
class Text extends AbstractBaseAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        static::$defaultAttributePostData['frontend_class'] = '';
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
                "{static::getFrontendInput()}_with_input_validation" => [
                    array_merge(static::$defaultAttributePostData, ['frontend_class' => 'validate-alpha']),
                ],
                "{static::getFrontendInput()}_without_input_validation" => [
                    static::$defaultAttributePostData,
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeDataWithCheckArray(): array
    {
        return array_merge_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{static::getFrontendInput()}_with_input_validation" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'frontend_class' => 'validate-alpha',
                    ],
                ],
                "{static::getFrontendInput()}_without_input_validation" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'frontend_class' => '',
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
                        'attribute_code' => 'varchar_attribute_update',
                    ],
                    'expectedData' => [
                        'attribute_code' => 'varchar_attribute',
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
        return 'text';
    }

    /**
     * @inheritdoc
     */
    protected static function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Varchar Attribute Update',
            ],
            'is_required' => '1',
            'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'default_value_text' => 'Varchar Attribute Default',
            'is_unique' => '1',
            'frontend_class' => 'validate-alphanum',
            'is_used_in_grid' => '1',
            'is_visible_in_grid' => '1',
            'is_filterable_in_grid' => '1',
            'is_searchable' => '1',
            'search_weight' => '2',
            'is_visible_in_advanced_search' => '1',
            'is_comparable' => '1',
            'is_used_for_promo_rules' => '1',
            'is_html_allowed_on_front' => '0',
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
        unset($updatePostData['default_value_text']);
        return array_merge(
            $updatePostData,
            [
                'frontend_label' => 'Varchar Attribute Update',
                'frontend_input' => 'text',
                'attribute_code' => 'varchar_attribute',
                'default_value' => 'Varchar Attribute Default',
                'is_filterable' => '0',
                'is_filterable_in_search' => '0',
                'position' => '0',
                'is_user_defined' => '1',
                'backend_type' => 'varchar',
            ]
        );
    }
}
