<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface CategoryLinkManagementInterface
{
    /**
     * Get products assigned to category
     *
     * @param int $categoryId
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkInterface[]
     */
    public function getAssignedProducts($categoryId);

    /**
     * Assign product to given categories
     *
     * @param string $productSku
     * @param int[] $categoryIds
     * @return bool
     * @since 101.0.0
     */
    public function assignProductToCategories($productSku, array $categoryIds);
}
