<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

interface CategoryLinkManagementInterface
{
    /**
     * Get products assigned to category
     *
     * @param int $categoryId
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkInterface[]
     */
    public function getAssignedProducts($categoryId);
}
