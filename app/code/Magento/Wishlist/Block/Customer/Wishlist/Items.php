<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block\Customer\Wishlist;

/**
 * Wishlist block customer items
 */
class Items extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve table column object list
     *
     * @return \Magento\Wishlist\Block\Customer\Wishlist\Item\Column[]
     */
    public function getColumns()
    {
        $columns = [];
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
            if ($child instanceof \Magento\Wishlist\Block\Customer\Wishlist\Item\Column && $child->isEnabled()) {
                $columns[] = $child;
            }
        }
        return $columns;
    }
}
