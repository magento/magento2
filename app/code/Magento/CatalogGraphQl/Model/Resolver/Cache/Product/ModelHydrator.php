<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Cache\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\GraphQlResolverCache\Model\Resolver\Result\HydratorInterface;

/**
 * Product resolver data hydrator to rehydrate propagated model.
 */
class ModelHydrator implements HydratorInterface
{
    /**
     * @var ProductFactory
     */
    private ProductFactory $productFactory;

    /**
     * @var Product[]
     */
    private array $products = [];

    /**
     * @var HydratorPool
     */
    private HydratorPool $hydratorPool;

    /**
     * @param ProductFactory $productFactory
     * @param HydratorPool $hydratorPool
     */
    public function __construct(
        ProductFactory $productFactory,
        HydratorPool   $hydratorPool
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->productFactory = $productFactory;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(array &$resolverData): void
    {
        if (array_key_exists('model_info', $resolverData)) {
            if (isset($this->products[$resolverData['model_info']['model_id']])) {
                $resolverData['model'] = $this->products[$resolverData['model_info']['model_id']];
            } else {
                $hydrator = $this->hydratorPool->getHydrator($resolverData['model_info']['model_entity_type']);
                $model = $this->productFactory->create();
                $hydrator->hydrate($model, $resolverData['model_info']['model_data']);
                $this->products[$resolverData['model_info']['model_id']] = $model;
                $resolverData['model'] = $this->products[$resolverData['model_info']['model_id']];
                unset($resolverData['model_info']);
            }
        }
    }
}
