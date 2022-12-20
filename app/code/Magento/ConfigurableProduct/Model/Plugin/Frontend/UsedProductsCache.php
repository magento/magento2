<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Plugin\Frontend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Cache of used products for configurable product
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UsedProductsCache
{
    /**
     * Default cache life time: 1 year
     */
    private const DEFAULT_CACHE_LIFE_TIME = 31536000;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var int
     */
    private $cacheLifeTime;

    /**
     * @param MetadataPool $metadataPool
     * @param FrontendInterface $cache
     * @param SerializerInterface $serializer
     * @param ProductInterfaceFactory $productFactory
     * @param Session $customerSession
     * @param int $cacheLifeTime
     */
    public function __construct(
        MetadataPool $metadataPool,
        FrontendInterface $cache,
        SerializerInterface $serializer,
        ProductInterfaceFactory $productFactory,
        Session $customerSession,
        int $cacheLifeTime = self::DEFAULT_CACHE_LIFE_TIME
    ) {
        $this->metadataPool = $metadataPool;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->productFactory = $productFactory;
        $this->customerSession = $customerSession;
        $this->cacheLifeTime = $cacheLifeTime;
    }

    /**
     * Retrieve used products for configurable product
     *
     * @param Configurable $subject
     * @param callable $proceed
     * @param Product $product
     * @param array|null $requiredAttributeIds
     * @return ProductInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetUsedProducts(
        Configurable $subject,
        callable $proceed,
        $product,
        $requiredAttributeIds = null
    ) {
        $cacheKey = $this->getCacheKey($product, $requiredAttributeIds);
        $usedProducts = $this->readUsedProductsCacheData($cacheKey);
        if ($usedProducts === null) {
            $usedProducts = $proceed($product, $requiredAttributeIds);
            $this->saveUsedProductsCacheData($product, $usedProducts, $cacheKey);
        }

        return $usedProducts;
    }

    /**
     * Generate cache key for product
     *
     * @param Product $product
     * @param array|null $requiredAttributeIds
     * @return string
     */
    private function getCacheKey($product, $requiredAttributeIds = null): string
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $keyParts = [
            'getUsedProducts',
            $product->getData($metadata->getLinkField()),
            $product->getStoreId(),
            $this->customerSession->getCustomerGroupId(),
        ];
        if ($requiredAttributeIds !== null) {
            sort($requiredAttributeIds);
            $keyParts[] = implode('', $requiredAttributeIds);
        }
        $cacheKey = sha1(implode('_', $keyParts));

        return $cacheKey;
    }

    /**
     * Read used products data from cache
     *
     * Looking for cache record stored under provided $cacheKey
     * In case data exists turns it into array of products
     *
     * @param string $cacheKey
     * @return ProductInterface[]|null
     */
    private function readUsedProductsCacheData(string $cacheKey): ?array
    {
        $data = $this->cache->load($cacheKey);
        if (!$data) {
            return null;
        }

        $items = $this->serializer->unserialize($data);
        if (!$items) {
            return null;
        }

        $usedProducts = [];
        foreach ($items as $item) {
            /** @var Product $productItem */
            $productItem = $this->productFactory->create();
            $productItem->setData($item);
            $usedProducts[] = $productItem;
        }

        return $usedProducts;
    }

    /**
     * Save $subProducts to cache record identified with provided $cacheKey
     *
     * Cached data will be tagged with combined list of product tags and data specific tags i.e. 'price' etc.
     *
     * @param Product $product
     * @param ProductInterface[] $subProducts
     * @param string $cacheKey
     * @return bool
     */
    private function saveUsedProductsCacheData(Product $product, array $subProducts, string $cacheKey): bool
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $data = $this->serializer->serialize(
            array_map(
                function ($item) {
                    return $item->getData();
                },
                $subProducts
            )
        );
        $tags = array_merge(
            $product->getIdentities(),
            [
                Category::CACHE_TAG,
                Product::CACHE_TAG,
                'price',
                Configurable::TYPE_CODE . '_' . $product->getData($metadata->getLinkField()),
            ]
        );
        $result = $this->cache->save($data, $cacheKey, $tags, $this->cacheLifeTime);

        return (bool) $result;
    }
}
