<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

/**
 * Replace-mode media gallery reconciliation for product import.
 */
class MediaGalleryReplaceCoordinator
{
    /**
     * @var bool
     */
    private bool $enabled = false;

    /**
     * @var array
     */
    private array $replaceSkus = [];

    /**
     * @var array
     */
    private array $keptPaths = [];

    /**
     * @var array
     */
    private array $uploadFailedSkus = [];

    /**
     * @var string|null
     */
    private ?string $productEntityLinkField = null;

    /**
     * @param MediaGalleryReplaceRolePlan $rolePlan
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly MediaGalleryReplaceRolePlan $rolePlan,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Configure replace mode for an import run.
     *
     * @param bool $enabled
     * @param string[] $roleAttributeCodes
     * @return void
     */
    public function configure(bool $enabled, array $roleAttributeCodes): void
    {
        $this->enabled = $enabled;
        $this->replaceSkus = [];
        $this->keptPaths = [];
        $this->uploadFailedSkus = [];
        $this->rolePlan->reset($enabled ? $roleAttributeCodes : []);
    }

    /**
     * Check if any SKUs were registered for replace.
     *
     * @return bool
     */
    public function hasRegisteredProducts(): bool
    {
        return $this->enabled && $this->replaceSkus !== [];
    }

    /**
     * Get registered replace SKUs.
     *
     * @return string[]
     */
    public function getRegisteredSkus(): array
    {
        return array_values($this->replaceSkus);
    }

    /**
     * Mark SKU when an image failed to load.
     *
     * @param string $sku
     * @return void
     */
    public function markUploadFailed(string $sku): void
    {
        if (!$this->enabled || $sku === '') {
            return;
        }
        $this->uploadFailedSkus[mb_strtolower($sku)] = true;
    }

    /**
     * Get SKUs whose gallery unlinks were skipped.
     *
     * @return string[]
     */
    public function getSkusWithSkippedRemovals(): array
    {
        $skus = [];
        foreach ($this->replaceSkus as $skuKey => $originalSku) {
            if (isset($this->uploadFailedSkus[$skuKey])) {
                $skus[] = $originalSku;
            }
        }
        return $skus;
    }

    /**
     * Merge CSV role assignments into the plan.
     *
     * @param array $assignments
     * @return void
     */
    public function planRoleAssignments(array $assignments): void
    {
        if ($this->enabled) {
            $this->rolePlan->mergeCsvAssignments($assignments);
        }
    }

    /**
     * Prefetch role attribute values for SKUs.
     *
     * @param string[] $skus
     * @return void
     */
    public function warmRolesCache(array $skus): void
    {
        if ($this->enabled) {
            $this->rolePlan->warm($skus);
        }
    }

    /**
     * Register SKU for replace when default-store row has additional_images.
     *
     * @param string $sku
     * @param array $rowData
     * @param int $storeId
     * @return void
     */
    public function registerProduct(string $sku, array $rowData, int $storeId): void
    {
        if (!$this->enabled
            || (int)$storeId !== Store::DEFAULT_STORE_ID
            || !$this->rowHasAdditionalImagesColumn($rowData)
        ) {
            return;
        }
        $this->replaceSkus[mb_strtolower($sku)] = $sku;
    }

    /**
     * Keep a resolved gallery path for the SKU.
     *
     * @param string $sku
     * @param string $normalizedPath
     * @return void
     */
    public function keepPath(string $sku, string $normalizedPath): void
    {
        $this->keptPaths[mb_strtolower($sku)][$normalizedPath] = true;
    }

    /**
     * Build gallery unlink list for registered SKUs.
     *
     * @param array $existingImages
     * @return array
     */
    public function collectRemovals(array $existingImages): array
    {
        if (!$this->hasRegisteredProducts()) {
            return [[], []];
        }

        $removals = [];
        $removedSkus = [];
        $seen = [];
        $linkField = $this->getProductEntityLinkField();

        foreach ($this->replaceSkus as $skuKey => $originalSku) {
            if (isset($this->uploadFailedSkus[$skuKey])) {
                continue;
            }
            $keep = $this->buildKeepSet($originalSku, $skuKey);
            $skuHadRemoval = false;

            foreach ($this->iterateImageEntries($existingImages, $skuKey, $linkField) as $imageData) {
                $pathNormalized = ltrim((string)($imageData['_path'] ?? ''), '/\\');
                $valueNormalized = ltrim((string)($imageData['value'] ?? $pathNormalized), '/\\');
                if (isset($keep[$pathNormalized]) || isset($keep[$valueNormalized])) {
                    continue;
                }
                $dedupeKey = $imageData['value_id'] . ':' . $imageData[$linkField];
                if (isset($seen[$dedupeKey])) {
                    continue;
                }
                $seen[$dedupeKey] = true;
                $skuHadRemoval = true;
                $filePath = (string)($imageData['value'] ?? $pathNormalized);
                $removals[] = [
                    'value_id' => $imageData['value_id'],
                    $linkField => $imageData[$linkField],
                    'value' => $filePath !== '' ? $filePath : $pathNormalized,
                ];
            }

            if ($skuHadRemoval) {
                $removedSkus[] = (string)$originalSku;
            }
        }

        return [$removals, array_values(array_unique($removedSkus))];
    }

    /**
     * Build keep-set from kept paths and protected roles.
     *
     * @param string $sku
     * @param string $skuKey
     * @return array
     */
    private function buildKeepSet(string $sku, string $skuKey): array
    {
        $keep = $this->keptPaths[$skuKey] ?? [];
        foreach ($this->rolePlan->protectedPaths($sku) as $path) {
            $keep[$path] = true;
        }
        return $keep;
    }

    /**
     * Yield removable image entries for a SKU.
     *
     * @param array $existingImages
     * @param string $skuKey
     * @param string $linkField
     * @return \Generator
     */
    private function iterateImageEntries(array $existingImages, string $skuKey, string $linkField): \Generator
    {
        foreach ($existingImages as $bySku) {
            if (!isset($bySku[$skuKey])) {
                continue;
            }
            foreach ($bySku[$skuKey] as $path => $imageData) {
                if (!isset($imageData['value_id'], $imageData[$linkField])) {
                    continue;
                }
                $mediaType = $imageData['media_type'] ?? null;
                if ($mediaType !== null && $mediaType !== '' && $mediaType !== 'image') {
                    continue;
                }
                $imageData['_path'] = $path;
                yield $imageData;
            }
        }
    }

    /**
     * Check if row has additional_images column.
     *
     * @param array $rowData
     * @return bool
     */
    private function rowHasAdditionalImagesColumn(array $rowData): bool
    {
        return array_key_exists(Product::COL_MEDIA_IMAGE, $rowData)
            || array_key_exists('additional_images', $rowData);
    }

    /**
     * Get product entity link field.
     *
     * @return string
     */
    private function getProductEntityLinkField(): string
    {
        if ($this->productEntityLinkField === null) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
