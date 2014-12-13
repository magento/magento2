<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Api\Data;

interface CategoryProductLinkInterface
{
    /**
     * @return string|null
     */
    public function getSku();

    /**
     * @return int|null
     */
    public function getPosition();

    /**
     * Get category id
     *
     * @return string
     */
    public function getCategoryId();
}
