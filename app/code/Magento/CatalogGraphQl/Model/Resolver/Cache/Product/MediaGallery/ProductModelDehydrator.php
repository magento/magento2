<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\GraphQlResolverCache\Model\Resolver\Result\DehydratorInterface;

/**
 * MediaGallery resolver data dehydrator to create snapshot data necessary to restore model.
 */
class ProductModelDehydrator implements DehydratorInterface
{
    /**
     * @var TypeResolver
     */
    private TypeResolver $typeResolver;

    /**
     * @var HydratorPool
     */
    private HydratorPool $hydratorPool;

    /**
     * @param HydratorPool $hydratorPool
     * @param TypeResolver $typeResolver
     */
    public function __construct(
        HydratorPool $hydratorPool,
        TypeResolver $typeResolver
    ) {
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
    }

    /**
     * @inheritdoc
     */
    public function dehydrate(array &$resolvedValue): void
    {
        if (count($resolvedValue) > 0) {
            $firstKey = array_key_first($resolvedValue);
            $this->dehydrateMediaGalleryEntity($resolvedValue[$firstKey]);
            foreach ($resolvedValue as $key => &$value) {
                if ($key !== $firstKey) {
                    unset($value['model']);
                }
            }
        }
    }

    /**
     * Dehydrate the resolved value of a media gallery entity.
     *
     * @param array $mediaGalleryEntityResolvedValue
     * @return void
     * @throws \Exception
     */
    private function dehydrateMediaGalleryEntity(array &$mediaGalleryEntityResolvedValue): void
    {
        if (array_key_exists('model', $mediaGalleryEntityResolvedValue)
            && $mediaGalleryEntityResolvedValue['model'] instanceof Product) {
            /** @var Product $model */
            $model = $mediaGalleryEntityResolvedValue['model'];
            $entityType = $this->typeResolver->resolve($model);
            $mediaGalleryEntityResolvedValue['model_info']['model_data'] = $this->hydratorPool->getHydrator($entityType)
                ->extract($model);
            $mediaGalleryEntityResolvedValue['model_info']['model_entity_type'] = $entityType;
            $mediaGalleryEntityResolvedValue['model_info']['model_id'] = $model->getId();
            unset($mediaGalleryEntityResolvedValue['model']);
        }
    }
}
