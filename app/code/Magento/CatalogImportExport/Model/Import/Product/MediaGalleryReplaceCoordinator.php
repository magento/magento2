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
 *
 * Import-scoped lifecycle:
 * 1. configure()
 * 2. Per bunch: planRoleAssignments, warmRolesCache, registerProduct, keepPath
 * 3. After all bunches: collectRemovals()
 *
 * A path is kept if it was resolved during media import (keepPath) or it remains
 * an image role after applying multi-store CSV role reassignments (role plan).
 *
 * If any image upload fails for a SKU, deferred gallery unlinks are skipped for
 * that SKU so a re-import can complete replace safely (partial adds may remain).
 */
class MediaGalleryReplaceCoordinator
{
    /**
     * Whether replace mode is active for this import.
     *
     * @var bool
     */
    private bool $enabled = false;

    /**
     * Lowercase SKU => original SKU
     *
     * @var array<string, string>
     */
    private array $replaceSkus = [];

    /**
     * Lowercase SKU => normalized path => true
     *
     * @var array<string, array<string, true>>
     */
    private array $keptPaths = [];

    /**
     * Lowercase SKU => true when at least one image failed to load for the SKU.
     *
     * @var array<string, true>
     */
    private array $uploadFailedSkus = [];

    /**
     * Product entity link field (row_id or entity_id).
     *
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
     * Whether any SKU was registered for replace during this import.
     *
     * @return bool
     */
    public function hasRegisteredProducts(): bool
    {
        return $this->enabled && $this->replaceSkus !== [];
    }

    /**
     * SKUs registered for replace (original casing).
     *
     * @return string[]
     */
    public function getRegisteredSkus(): array
    {
        return array_values($this->replaceSkus);
    }

    /**
     * Record that an image could not be loaded for the SKU (any store row).
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
     * Registered replace SKUs that must not be unlinked (media load failure).
     *
     * @return string[] Original SKU casing
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
     * Merge image-role column values from a bunch into the role plan.
     *
     * @param array $assignments Lowercase sku => store_id => attribute_code => value
     * @return void
     */
    public function planRoleAssignments(array $assignments): void
    {
        if ($this->enabled) {
            $this->rolePlan->mergeCsvAssignments($assignments);
        }
    }

    /**
     * Prefetch role attribute values for SKUs (first sight wins).
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
     * Mark SKU for replace when default-store row includes additional_images.
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
     * Record a resolved gallery path to keep for the SKU.
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
     * Build unlink list for registered SKUs without upload failures.
     *
     * @param array $existingImages storeId => sku => path => imageData
     * @return array{0: array, 1: string[]}
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
            // Partial media failure: keep existing gallery so re-import can finish replace.
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
                $removals[] = [
                    'value_id' => $imageData['value_id'],
                    $linkField => $imageData[$linkField],
                ];
            }

            if ($skuHadRemoval) {
                $removedSkus[] = (string)$originalSku;
            }
        }

        return [$removals, array_values(array_unique($removedSkus))];
    }

    /**
     * Build keep-set from kept media paths and protected role paths.
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
     * Yield removable image entries for a SKU (dedupe by store is handled via value_id).
     *
     * @param array $existingImages
     * @param string $skuKey
     * @param string $linkField
     * @return \Generator<int, array>
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
     * Whether the row includes the additional_images column.
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
     * Product entity link field (row_id or entity_id).
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
