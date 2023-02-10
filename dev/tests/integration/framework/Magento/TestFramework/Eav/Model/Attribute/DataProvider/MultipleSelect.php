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
 * Product attribute data for attribute with input type multiple select.
 */
class MultipleSelect extends AbstractAttributeDataWithOptions
{
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
                        'attribute_code' => 'multiselect_attribute',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function getFrontendInput(): string
    {
        return 'multiselect';
    }

    /**
     * @inheritdoc
     */
    protected function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Multiselect Attribute Update',
            ],
            'frontend_input' => 'multiselect',
            'is_required' => '1',
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
            'is_html_allowed_on_front' => '0',
            'is_visible_on_front' => '1',
            'used_in_product_listing' => '1',
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
                'frontend_label' => 'Multiselect Attribute Update',
                'attribute_code' => 'multiselect_attribute',
                'default_value' => null,
                'frontend_class' => null,
                'used_for_sort_by' => '0',
                'is_user_defined' => '1',
                'backend_type' => 'text',
            ]
        );
    }
}
