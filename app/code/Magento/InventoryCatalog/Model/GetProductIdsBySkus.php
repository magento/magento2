<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

/**
 * @inheritdoc
 */
class GetProductIdsBySkus implements GetProductIdsBySkusInterface
{
    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ProductResourceModel $productResource
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ProductResourceModel $productResource,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->productResource = $productResource;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus): array
    {
        $cacheKey = hash('md5', implode(',', $skus));

        if ($cachedIds = $this->cache->load($cacheKey)) {
            return $this->serializer->unserialize($cachedIds);
        }

        $idsBySkus = $this->productResource->getProductsIdsBySkus($skus);
        $notFoundedSkus = array_diff($skus, array_keys($idsBySkus));

        if (!empty($notFoundedSkus)) {
            throw new NoSuchEntityException(
                __('Following products with requested skus were not found: %1', implode($notFoundedSkus, ', '))
            );
        }

        $this->cache->save($this->serializer->serialize($idsBySkus), $cacheKey);

        return $idsBySkus;
    }
}
