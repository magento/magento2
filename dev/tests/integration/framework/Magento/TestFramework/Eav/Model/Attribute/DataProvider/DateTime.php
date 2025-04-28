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
 * Product attribute data for attribute with input type datetime.
 */
class DateTime extends AbstractBaseAttributeData
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
                        'default_value_datetime' => '02/4/2020 6:30 AM',
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeDataWithCheckArray(): array
    {
        return array_replace_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{static::getFrontendInput()}_with_default_value" => [
                    1 => [
                        'default_value' => '2020-02-04 06:30:00',
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
                        'attribute_code' => 'datetime_attribute',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getUpdateProviderWithErrorMessage(): array
    {
        $frontendInput = static::getFrontendInput();
        return array_replace_recursive(
            parent::getUpdateProviderWithErrorMessage(),
            [
                "{$frontendInput}_wrong_default_value" => [
                    'postData' => [
                        'default_value_datetime' => '//02/4/2020 6:30 AM',
                    ],
                    'errorMessage' => (string)__('The default date is invalid. Verify the date and try again.'),
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected static function getFrontendInput(): string
    {
        return 'datetime';
    }

    /**
     * @inheritdoc
     */
    protected static function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Date Time Attribute Update',
            ],
            'frontend_input' => 'datetime',
            'is_required' => '1',
            'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'default_value_datetime' => '02/4/2020 6:30 AM',
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
        unset($updatePostData['default_value_datetime']);
        return array_merge(
            $updatePostData,
            [
                'frontend_label' => 'Date Time Attribute Update',
                'attribute_code' => 'datetime_attribute',
                'default_value' => '2020-02-04 06:30:00',
                'frontend_class' => null,
                'is_filterable' => '0',
                'is_filterable_in_search' => '0',
                'position' => '0',
                'is_user_defined' => '1',
                'backend_type' => 'datetime',
            ]
        );
    }
}
