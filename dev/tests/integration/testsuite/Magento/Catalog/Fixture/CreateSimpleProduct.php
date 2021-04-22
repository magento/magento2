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
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Create simple product fixture
 */
class CreateSimpleProduct implements RevertibleDataFixtureInterface
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
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(
        ServiceFactory $serviceFactory
    ) {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'save');
        $result = $service->execute(
            [
                'product' => array_merge(self::DEFAULT_DATA, $data)
            ]
        );

        return [
            'product' => $result
        ];
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'deleteById');
        $service->execute(
            [
                'sku' => $data['product']->getSku()
            ]
        );
    }
}
