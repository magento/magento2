<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Fixture;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\TestFramework\Fixture\AbstractApiDataFixture;
use Magento\TestFramework\Fixture\ApiDataFixtureInterface;

/**
 * Create product attribute fixture
 */
class CreateProductAttribute extends AbstractApiDataFixture
{
    private const DEFAULT_DATA = [
        'is_wysiwyg_enabled' => false,
        'is_html_allowed_on_front' => true,
        'used_for_sort_by' => false,
        'is_filterable' => false,
        'is_filterable_in_search' => false,
        'is_used_in_grid' => true,
        'is_visible_in_grid' => true,
        'is_filterable_in_grid' => true,
        'position' => 0,
        'apply_to' => [],
        'is_searchable' => '0',
        'is_visible_in_advanced_search' => '0',
        'is_comparable' => '0',
        'is_used_for_promo_rules' => '0',
        'is_visible_on_front' => '0',
        'used_in_product_listing' => '0',
        'is_visible' => true,
        'scope' => 'store',
        'attribute_code' => 'fixture_attribute',
        'frontend_input' => 'text',
        'entity_type_id' => '4',
        'is_required' => false,
        'options' => [],
        'is_user_defined' => true,
        'default_frontend_label' => 'Fixture Attribute',
        'frontend_labels' => [],
        'backend_type' => 'varchar',
        'is_unique' => '0',
        'validation_rules' => []
    ];

    /**
     * @inheritdoc
     */
    public function getService(): array
    {
        return [
            ApiDataFixtureInterface::SERVICE_CLASS => ProductAttributeRepositoryInterface::class,
            ApiDataFixtureInterface::SERVICE_METHOD => 'save',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRollbackService(): array
    {
        return [
            ApiDataFixtureInterface::SERVICE_CLASS => ProductAttributeRepositoryInterface::class,
            ApiDataFixtureInterface::SERVICE_METHOD => 'deleteById',
        ];
    }

    /**
     * @inheritdoc
     */
    public function processServiceMethodParameters(array $data): array
    {
        return [
            'attribute' => array_merge(self::DEFAULT_DATA, $data)
        ];
    }

    /**
     * @inheritdoc
     */
    public function processRollbackServiceMethodParameters(array $data): array
    {
        return [
            'attributeCode' => $data['attribute']->getAttributeCode()
        ];
    }

    /**
     * @inheritdoc
     */
    public function processServiceResult(array $data, $result): array
    {
        return [
            'attribute' => $result
        ];
    }
}
