<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Weee\Model\Attribute\DataProvider;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\TestFramework\Eav\Model\Attribute\DataProvider\AbstractBaseAttributeData;
use Magento\Store\Model\Store;
use Magento\Weee\Model\Attribute\Backend\Weee\Tax;

/**
 * Product attribute data for attribute with input type fixed product tax.
 */
class FixedProductTax extends AbstractBaseAttributeData
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
        static::$defaultAttributePostData['used_for_sort_by'] = '0';
        $result = parent::getAttributeData();
        unset($result["{static::getFrontendInput()}_with_default_value"]);
        unset($result["{static::getFrontendInput()}_without_default_value"]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public static function getAttributeDataWithCheckArray(): array
    {
        static::$defaultAttributePostData['used_for_sort_by'] = '0';
        $result = parent::getAttributeDataWithCheckArray();
        unset($result["{static::getFrontendInput()}_with_default_value"]);
        unset($result["{static::getFrontendInput()}_without_default_value"]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public static function getUpdateProvider(): array
    {
        static::$defaultAttributePostData['used_for_sort_by'] = '0';
        $frontendInput = static::getFrontendInput();
        return array_replace_recursive(
            parent::getUpdateProvider(),
            [
                "{$frontendInput}_other_attribute_code" => [
                    'postData' => [
                        'attribute_code' => 'text_attribute_update',
                    ],
                    'expectedData' => [
                        'attribute_code' => 'fixed_product_attribute',
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
        return 'weee';
    }

    /**
     * @inheritdoc
     */
    protected static function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Fixed product tax Update',
            ],
            'frontend_input' => 'weee',
            'is_used_in_grid' => '1',
            'is_visible_in_grid' => '1',
            'is_filterable_in_grid' => '1',
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
                'frontend_label' => 'Fixed product tax Update',
                'is_required' => '0',
                'attribute_code' => 'fixed_product_attribute',
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'default_value' => null,
                'is_unique' => '0',
                'frontend_class' => null,
                'is_searchable' => '0',
                'search_weight' => '1',
                'is_visible_in_advanced_search' => '0',
                'is_comparable' => '0',
                'is_filterable' => '0',
                'is_filterable_in_search' => '0',
                'position' => '0',
                'is_used_for_promo_rules' => '0',
                'is_html_allowed_on_front' => '0',
                'is_visible_on_front' => '0',
                'used_in_product_listing' => '0',
                'used_for_sort_by' => '0',
                'is_user_defined' => '1',
                'backend_type' => 'static',
                'backend_model' => Tax::class,
            ]
        );
    }
}
