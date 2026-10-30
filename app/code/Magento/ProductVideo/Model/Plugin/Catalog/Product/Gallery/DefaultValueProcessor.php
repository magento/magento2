<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\DefaultValueProcessor as CatalogUseDefaultDataProvider;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\ProductVideo\Model\ResourceModel\Video;
use Magento\Store\Model\Store;

class DefaultValueProcessor
{
    /**
     * @param Video $videoResourceModel
     */
    public function __construct(
        private readonly Video $videoResourceModel
    ) {
    }

    /**
     * Adds "use default" flag for video title and description
     *
     * @param CatalogUseDefaultDataProvider $subject
     * @param array|null $result
     * @param Product $product
     * @param array|null $data
     * @param int|null $storeId
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        CatalogUseDefaultDataProvider $subject,
        ?array $result,
        Product $product,
        ?array $data = null,
        ?int $storeId = null
    ): ?array {
        $images = $this->getImages($result);
        $storeId = $storeId ?? (int)$product->getStoreId();
        if ($storeId === Store::DEFAULT_STORE_ID || empty($images)) {
            return $result;
        }

        $ids = $this->collectVideoValueIds($images);
        if (empty($ids)) {
            return $result;
        }

        $storeValues = $this->getStoreValues($ids, $storeId);

        foreach ($images as &$image) {
            if (!$this->isProcessable($image)) {
                continue;
            }
            $image['video_title_use_default'] = 1;
            $image['video_description_use_default'] = 1;
            if (isset($storeValues[$image['value_id']])) {
                $storeValue = $storeValues[$image['value_id']];
                $image['video_title_use_default'] = $storeValue['title'] === null ? 1 : 0;
                $image['video_description_use_default'] = $storeValue['description'] === null ? 1 : 0;
            }
        }
        $result['images'] = $images;

        return $result;
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
            && ($image['media_type'] ?? null) === ExternalVideoEntryConverter::MEDIA_TYPE_CODE
            // skip already processed media entries
            && !isset($image['video_title_use_default'], $image['video_description_use_default']);
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
     * Collect video value IDs from images data
     *
     * @param array $images
     * @return array
     */
    private function collectVideoValueIds(array $images): array
    {
        $ids = [];
        foreach ($images as $image) {
            if ($this->isProcessable($image)) {
                $ids[] = $image['value_id'];
            }
        }
        return $ids;
    }

    /**
     * Get video store values by value IDs and store ID
     *
     * @param array $ids
     * @param int $storeId
     * @return array
     */
    private function getStoreValues(array $ids, int $storeId): array
    {
        $storeValues = [];
        foreach ($this->videoResourceModel->loadByIdsAndStoreId($ids, $storeId) as $item) {
            $storeValues[$item['value_id']] = $item;
        }
        return $storeValues;
    }
}
