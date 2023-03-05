<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer\Wishlist;

use Magento\Framework\View\Element\Template;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Column;

/**
 * Wishlist block customer items
 *
 * @api
 * @since 100.0.2
 */
class Items extends Template
{
    /**
     * Retrieve table column object list
     *
     * @return Column[]
     */
    public function getColumns()
    {
        $columns = [];
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
            if ($child instanceof Column && $child->isEnabled()) {
                $columns[] = $child;
            }
        }
        return $columns;
    }
}
