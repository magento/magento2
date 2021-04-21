<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Fixture;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Create simple product fixture
 */
class CreateSimpleProduct implements RevertibleDataFixtureInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ServiceInputProcessor
     */
    private $productHydrator;

    /**
     * @var array
     */
    private $defaultData = [
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
     * @param ProductRepositoryInterface $productRepository
     * @param ServiceInputProcessor $productHydrator
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ServiceInputProcessor $productHydrator
    ) {
        $this->productRepository = $productRepository;
        $this->productHydrator = $productHydrator;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        /** @var $product Product */
        $data = array_merge($this->defaultData, $data);
        $product = $this->productHydrator->convertValue($data, ProductInterface::class);
        $this->productRepository->save($product);

        return [
            'product' => $product
        ];
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        try {
            /** @var $product Product */
            $product = $data['product'];
            $this->productRepository->deleteById($product->getSku());
        } catch (NoSuchEntityException $e) {
            //ignore
        }
    }
}
