<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @codeCoverageIgnore
 */
class ProductLinkSearchResults extends \Magento\Framework\Api\SearchResults
{
    /**
     * Get items
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     */
    public function getItems()
    {
        return parent::getItems();
    }
}
