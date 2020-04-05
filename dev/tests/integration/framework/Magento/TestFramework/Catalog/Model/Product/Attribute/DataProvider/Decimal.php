<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Attribute\DataProvider;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\TestFramework\Eav\Model\Attribute\DataProvider\AbstractBaseAttributeData;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Product\Attribute\Backend\Price as BackendPrice;

/**
 * Product attribute data for attribute with input type weee.
 */
class Decimal extends AbstractBaseAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData['is_filterable'] = '0';
        $this->defaultAttributePostData['is_filterable_in_search'] = '0';
        $this->defaultAttributePostData['used_for_sort_by'] = '0';
    }

    /**
     * @inheritdoc
     */
    public function getAttributeData(): array
    {
        $result = parent::getAttributeData();
        unset($result["{$this->getFrontendInput()}_with_default_value"]);
        unset($result["{$this->getFrontendInput()}_without_default_value"]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeDataWithCheckArray(): array
    {
        $result = parent::getAttributeDataWithCheckArray();
        unset($result["{$this->getFrontendInput()}_with_default_value"]);
        unset($result["{$this->getFrontendInput()}_without_default_value"]);

        return $result;
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
                        'attribute_code' => 'decimal_attribute',
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
        return 'price';
    }

    /**
     * @inheritdoc
     */
    protected function getUpdatePostData(): array
    {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Decimal Attribute Update',
            ],
            'frontend_input' => 'price',
            'is_required' => '1',
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
            'is_html_allowed_on_front' => '1',
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
                'frontend_label' => 'Decimal Attribute Update',
                'attribute_code' => 'decimal_attribute',
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'default_value' => null,
                'frontend_class' => null,
                'is_user_defined' => '1',
                'backend_type' => 'decimal',
                'backend_model' => BackendPrice::class,
            ]
        );
    }
}
