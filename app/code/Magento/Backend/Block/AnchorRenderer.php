<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Framework\Escaper;

/**
 * Class AnchorRenderer
 * @since 2.2.0
 */
class AnchorRenderer
{
    /**
     * @var MenuItemChecker
     * @since 2.2.0
     */
    private $menuItemChecker;

    /**
     * @var Escaper
     * @since 2.2.0
     */
    private $escaper;

    /**
     * @param MenuItemChecker $menuItemChecker
     * @param Escaper $escaper
     * @since 2.2.0
     */
    public function __construct(
        MenuItemChecker $menuItemChecker,
        Escaper $escaper
    ) {
        $this->menuItemChecker = $menuItemChecker;
        $this->escaper = $escaper;
    }

    /**
     * Render menu item anchor.
     *
     *  It is used in backend menu to render anchor menu.
     *
     * @param Item|false $activeItem Can be false if menu item is inaccessible
     * but was triggered directly using controller. It is a legacy code behaviour.
     * @param Item $menuItem
     * @param int $level
     * @return string
     * @since 2.2.0
     */
    public function renderAnchor($activeItem, Item $menuItem, $level)
    {
        if ($level == 1 && $menuItem->getUrl() == '#') {
            $output = '';
            if ($menuItem->hasChildren()) {
                $output = '<strong class="submenu-group-title" role="presentation">'
                    . '<span>' . $this->escaper->escapeHtml(__($menuItem->getTitle())) . '</span>'
                    . '</strong>';
            }
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function _renderItemOnclickFunction($menuItem)
    {
        return $menuItem->hasClickCallback() ? ' onclick="' . $menuItem->getClickCallback() . '"' : '';
    }
}
