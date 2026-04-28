<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\MediaGalleryValue;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

class DefaultValueProcessor
{
    /**
     * @param MediaGalleryValue $mediaGalleryValueResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly MediaGalleryValue $mediaGalleryValueResource,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Check if product media gallery for specific store is using default values
     *
     * @param Product $product
     * @param array|null $data Media gallery data
     * @param int|null $storeId
     * @return array|null
     */
    public function process(Product $product, ?array $data = null, ?int $storeId = null): ?array
    {
        $data ??= $product->getData('media_gallery');
        $storeId ??= (int) $product->getStoreId();
        $images = $this->getImages($data);
        if ($storeId === Store::DEFAULT_STORE_ID || array_filter($images, $this->isProcessable(...)) === []) {
            return $data;
        }

        $storeValues = $this->getStoreValues($product, $storeId);
        $useDefaultSorting = 1;
        foreach ($images as &$image) {
            if (!$this->isProcessable($image)) {
                continue;
            }
            $image['label_use_default'] = 1;
            $image['disabled_use_default'] = 1;
            $image['position_use_default'] = &$useDefaultSorting;
            if (isset($storeValues[$image['value_id']])) {
                $storeValue = $storeValues[$image['value_id']];
                $image['label_use_default'] = $storeValue['label'] === null ? 1 : 0;
                $image['disabled_use_default'] = $storeValue['disabled'] === null ? 1 : 0;
                $useDefaultSorting = $storeValue['position'] === null ? $useDefaultSorting : 0;
            }
        }
        $data['images'] = $images;
        
        return $data;
    }

    /**
     * Check if media gallery image is valid for processing
     *
     * @param array $image
     * @return bool
     */
    private function isProcessable(array $image): bool
    {
        return !empty($image['value_id'])
            // Check if media gallery images are not already processed
            && !isset($image['label_use_default'], $image['disabled_use_default'], $image['position_use_default']);
    }

    /**
     * Get images data from media gallery data
     *
     * @param array|null $data
     * @return array
     */
    private function getImages(?array $data): array
    {
        return $data === null || !isset($data['images']) || !is_array($data['images']) ? [] : $data['images'];
    }

    /**
     * Get store specific media gallery values
     *
     * @param Product $product
     * @param int $storeId
     * @return array
     * @throws \Exception
     */
    private function getStoreValues(Product $product, int $storeId): array
    {
        $storeValues = [];
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $productId = (int) $product->getData($metadata->getLinkField());
        foreach ($this->mediaGalleryValueResource->getAllByEntityIdAndStoreId($productId, $storeId) as $item) {
            $storeValues[$item['value_id']] = $item;
        }
        return $storeValues;
    }
}
