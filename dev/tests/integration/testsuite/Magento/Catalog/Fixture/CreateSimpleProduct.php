<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Fixture\AbstractApiDataFixture;
use Magento\TestFramework\Fixture\ApiDataFixtureInterface;

/**
 * Create simple product fixture
 */
class CreateSimpleProduct extends AbstractApiDataFixture
{
    private const DEFAULT_DATA = [
        'type_id' => Type::TYPE_SIMPLE,
        'attribute_set_id' => 4,
        'name' => 'Simple Product',
        'sku' => 'simple',
        'price' => 10,
        'weight' => 1,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'custom_attributes' => [
            [
                'attribute_code' => 'tax_class_id',
                'value' => '2',
            ]
        ],
        'extension_attributes' => [
            'website_ids' => [1],
            'stock_item' => [
                'use_config_manage_stock' => true,
                'qty' => 100,
                'is_qty_decimal' => false,
                'is_in_stock' => true,
            ]
        ],
    ];

    /**
     * @inheritdoc
     */
    public function getService(): array
    {
        return [
            ApiDataFixtureInterface::SERVICE_CLASS => ProductRepositoryInterface::class,
            ApiDataFixtureInterface::SERVICE_METHOD => 'save',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRollbackService(): array
    {
        return [
            ApiDataFixtureInterface::SERVICE_CLASS => ProductRepositoryInterface::class,
            ApiDataFixtureInterface::SERVICE_METHOD => 'deleteById',
        ];
    }

    /**
     * @inheritdoc
     */
    public function processServiceMethodParameters(array $data): array
    {
        return [
            'product' => array_merge_recursive(self::DEFAULT_DATA, $data)
        ];
    }

    /**
     * @inheritdoc
     */
    public function processRollbackServiceMethodParameters(array $data): array
    {
        return [
            'sku' => $data['product']->getSku()
        ];
    }

    /**
     * @param $result
     * @inheritdoc
     */
    public function processServiceResult(array $data, $result): array
    {
        return [
            'product' => $result
        ];
    }
}
