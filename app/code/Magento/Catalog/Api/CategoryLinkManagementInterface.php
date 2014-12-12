<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
