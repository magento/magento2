<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

interface CategoryManagementInterface
{
    /**
     * Retrieve list of categories
     *
     * @param int $rootCategoryId
     * @param int $depth
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface containing Tree objects
     */
    public function getTree($rootCategoryId = null, $depth = null);

    /**
     * Move category
     *
     * @param int $categoryId
     * @param int $parentId
     * @param int $afterId
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function move($categoryId, $parentId, $afterId = null);
}
