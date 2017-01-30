<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Framework\Escaper;

/**
 * Class AnchorRenderer
 */
class AnchorRenderer
{
    /**
     * @var MenuItemChecker
     */
    private $menuItemChecker;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param MenuItemChecker $menuItemChecker
     * @param Escaper $escaper
     */
    public function __construct(
        MenuItemChecker $menuItemChecker,
        Escaper $escaper
    ) {
        $this->menuItemChecker = $menuItemChecker;
        $this->escaper = $escaper;
    }

    /**
     * Render menu item anchor
     *
     * @param Item|null $activeItem
     * @param Item $menuItem
     * @param int $level
     * @return string
     */
    public function renderAnchor($activeItem, Item $menuItem, $level)
    {
        if ($level == 1 && $menuItem->getUrl() == '#') {
            $output = '<strong class="submenu-group-title" role="presentation">'
                . '<span>' . $this->escaper->escapeHtml(__($menuItem->getTitle())) . '</span>'
                . '</strong>';
        } else {
            $target = $menuItem->getTarget() ? ('target=' . $menuItem->getTarget()) : '';
            $output = '<a href="' . $menuItem->getUrl() . '" ' . $target . ' ' . $this->_renderItemAnchorTitle(
                $menuItem
            ) . $this->_renderItemOnclickFunction(
                $menuItem
            ) . ' class="' . ($this->menuItemChecker->isItemActive($activeItem, $menuItem, $level) ? '_active' : '')
                . '">' . '<span>' . $this->escaper->escapeHtml(__($menuItem->getTitle()))
                . '</span>' . '</a>';
        }

        return $output;
    }

    /**
     * Render menu item anchor title
     *
     * @param Item $menuItem
     * @return string
     */
    private function _renderItemAnchorTitle($menuItem)
    {
        return $menuItem->hasTooltip() ? 'title="' . __($menuItem->getTooltip()) . '"' : '';
    }

    /**
     * Render menu item onclick function
     *
     * @param Item $menuItem
     * @return string
     */
    private function _renderItemOnclickFunction($menuItem)
    {
        return $menuItem->hasClickCallback() ? ' onclick="' . $menuItem->getClickCallback() . '"' : '';
    }
}
