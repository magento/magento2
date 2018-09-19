<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Check whether given menu item is currently selected.
     *
     * It is used in backend menu to highlight active menu item.
     *
     * @param Item|false $activeItem Can be false if menu item is inaccessible
     * but was triggered directly using controller. It is a legacy code behaviour.
     * @param Item $item
     * @param int $level
     * @return bool
     */
    public function isItemActive($activeItem, Item $item, $level)
    {
        $output = false;

        if ($level == 0
            && $activeItem instanceof \Magento\Backend\Model\Menu\Item
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
