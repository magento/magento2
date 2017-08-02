<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 2.0.0
 */
interface CategoryLinkManagementInterface
{
    /**
     * Get products assigned to category
     *
     * @param int $categoryId
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkInterface[]
     * @since 2.0.0
     */
    public function getAssignedProducts($categoryId);

    /**
     * Assign product to given categories
     *
     * @param string $productSku
     * @param int[] $categoryIds
     * @return bool
     * @since 2.1.0
     */
    public function assignProductToCategories($productSku, array $categoryIds);
}
