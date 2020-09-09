<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Attribute\DataProvider;

use Magento\Catalog\Model\Product\Attribute\Frontend\Image;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Eav\Model\Attribute\DataProvider\AbstractBaseAttributeData;

/**
 * Product attribute data for attribute with input type media image.
 */
class MediaImage extends AbstractBaseAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
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
                        'attribute_code' => 'image_attribute',
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
        return 'media_image';
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
            'frontend_input' => 'media_image',
            'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'is_used_in_grid' => '1',
            'is_visible_in_grid' => '1',
            'is_filterable_in_grid' => '1',
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
                'is_required' => '0',
                'attribute_code' => 'image_attribute',
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
                'is_html_allowed_on_front' => '1',
                'is_visible_on_front' => '0',
                'used_in_product_listing' => '1',
                'used_for_sort_by' => '0',
                'is_user_defined' => '1',
                'backend_type' => 'varchar',
                'frontend_model' => Image::class,
            ]
        );
    }
}
