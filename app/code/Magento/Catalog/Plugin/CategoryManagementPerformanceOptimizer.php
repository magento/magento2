<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;

/**
 * Performance optimizer plugin for CategoryManagement
 */
class CategoryManagementPerformanceOptimizer
{
    private const DEFAULT_MAX_DEPTH = 3; // Limit depth to prevent timeouts

    /**
     * Optimize getTree method with depth limits to prevent timeouts
     *
     * @param CategoryManagementInterface $subject
     * @param int|null $rootCategoryId
     * @param int|null $depth
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetTree(
        CategoryManagementInterface $subject,
        $rootCategoryId = null,
        $depth = null
    ): array {
        // Limit depth to prevent performance issues
        $depth = $depth ?? self::DEFAULT_MAX_DEPTH;
        return [$rootCategoryId, $depth];
    }
}
