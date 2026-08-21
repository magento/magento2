<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

/**
 * Protects image-role gallery paths during replace import.
 */
class MediaGalleryReplaceRolePlan
{
    /**
     * @var string[]
     */
    private array $roleAttributeCodes = [];

    /**
     * @var array
     */
    private array $dbRoles = [];

    /**
     * @var array
     */
    private array $csvOverrides = [];

    /**
     * @param MediaGalleryProcessor $mediaProcessor
     */
    public function __construct(
        private readonly MediaGalleryProcessor $mediaProcessor
    ) {
    }

    /**
     * Reset plan state for a new import run.
     *
     * @param string[] $roleAttributeCodes
     * @return void
     */
    public function reset(array $roleAttributeCodes): void
    {
        $this->roleAttributeCodes = $roleAttributeCodes;
        $this->dbRoles = [];
        $this->csvOverrides = [];
    }

    /**
     * Merge CSV role column values into the plan.
     *
     * @param array $assignments
     * @return void
     */
    public function mergeCsvAssignments(array $assignments): void
    {
        foreach ($assignments as $skuKey => $byStore) {
            foreach ($byStore as $storeId => $byCode) {
                foreach ($byCode as $code => $value) {
                    $this->csvOverrides[(string)$skuKey][(int)$storeId][(string)$code] = $value;
                }
            }
        }
    }

    /**
     * Prefetch role values for SKUs not yet loaded.
     *
     * @param string[] $skus
     * @return void
     */
    public function warm(array $skus): void
    {
        if ($this->roleAttributeCodes === [] || $skus === []) {
            return;
        }
        $toLoad = [];
        foreach (array_unique($skus) as $sku) {
            $sku = (string)$sku;
            if ($sku === '' || isset($this->dbRoles[mb_strtolower($sku)])) {
                continue;
            }
            $toLoad[] = $sku;
        }
        if ($toLoad === []) {
            return;
        }
        foreach ($this->mediaProcessor->getProductImageRoles($toLoad, $this->roleAttributeCodes) as $skuKey => $roles) {
            $this->dbRoles[$skuKey] = $roles;
        }
    }

    /**
     * Gallery paths protected by remaining image roles.
     *
     * @param string $sku
     * @return string[]
     */
    public function protectedPaths(string $sku): array
    {
        if ($this->roleAttributeCodes === []) {
            return [];
        }
        $skuKey = mb_strtolower($sku);
        if (!isset($this->dbRoles[$skuKey])) {
            $this->warm([$sku]);
            $this->dbRoles[$skuKey] = $this->dbRoles[$skuKey] ?? [];
        }

        $protected = [];
        foreach ($this->roleAttributeCodes as $code) {
            foreach ($this->dbRoles[$skuKey][$code] ?? [] as $storeId => $path) {
                if (!$path || $path === 'no_selection') {
                    continue;
                }
                if (array_key_exists($code, $this->csvOverrides[$skuKey][(int)$storeId] ?? [])) {
                    continue;
                }
                $protected[ltrim((string)$path, '/\\')] = true;
            }
        }
        return array_keys($protected);
    }
}
