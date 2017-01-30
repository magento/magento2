<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

use Magento\Backend\Model\Menu\Item;

/**
 * Class MenuItemChecker
 */
class MenuItemChecker
{
    /**
     * Check whether given item is currently selected
     *
     * @param Item $activeItem,
     * @param Item $item
     * @param int $level
     * @return bool
     */
    public function isItemActive(
        Item $activeItem,
        Item $item,
        $level
    ) {
        $output = false;

        if ($level == 0
            && $this->isActiveItemEqualOrChild($activeItem, $item)
        ) {
            $output = true;
        }
        return $output;
    }

    /**
     * @param Item $activeItem,
     * @param Item $item
     * @return bool
     */
    private function isActiveItemEqualOrChild($activeItem, $item)
     {
        return ($activeItem->getId() == $item->getId())
        || ($item->getChildren()->get($activeItem->getId()) !== null);
     }
}
