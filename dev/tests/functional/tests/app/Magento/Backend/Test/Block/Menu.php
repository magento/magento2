<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Top menu navigation block.
 */
class Menu extends Block
{
    /**
     * Main menu selector.
     *
     * @var string
     */
    protected $mainMenu = './/li[@role="menu-item"]/a[span="%s"]';

    /**
     * Submenu selector.
     *
     * @var string
     */
    protected $subMenu = './/li[@role="menu-item" and a[span="%s"]]/div[contains(@class, "submenu")]';

    /**
     * Submenu item selector.
     *
     * @var string
     */
    protected $subMenuItem = '//li[@role="menu-item"]//a[span="%s"]';

    /**
     * Parent menu item.
     *
     * @var string
     */
    protected $parentMenuLevel = 'li.parent.level-0:nth-of-type(%s)';

    /**
     * Returns array of parent menu items present on dashboard menu.
     *
     * @return array
     */
    public function getTopMenuItems()
    {
        $navigationMenu = $this->_rootElement;
        $menuItems = [];
        $counter = 1;
        $textSelector = 'a span';
        while ($navigationMenu->find(sprintf($this->parentMenuLevel, $counter))->isVisible()) {
            $menuItems[] = strtolower(
                $navigationMenu->find(sprintf($this->parentMenuLevel, $counter))
                    ->find($textSelector)
                    ->getText()
            );
            $counter++;
        }
        return $menuItems;
    }

    /**
     * Open backend page via menu.
     *
     * @param string $menuItem
     * @return void
     * @throws \Exception
     */
    public function navigate($menuItem)
    {
        $menuChain = array_map('trim', explode('>', $menuItem));
        $mainMenu = $menuChain[0];
        $subMenu = isset($menuChain[1]) ? $menuChain[1] : null;

        // Click on element in main menu
        $mainMenuElement = $this->_rootElement->find(sprintf($this->mainMenu, $mainMenu), Locator::SELECTOR_XPATH);
        if (!$mainMenuElement->isVisible()) {
            throw new \Exception('Main menu item "' . $mainMenu . '" is not visible.');
        }
        $mainMenuElement->click();

        // Click on element in submenu
        if ($subMenu === null) {
            return;
        }
        $subMenuSelector = sprintf($this->subMenu, $mainMenu);
        $this->waitForElementVisible($subMenuSelector, Locator::SELECTOR_XPATH);
        $subMenuItem = $subMenuSelector . sprintf($this->subMenuItem, $subMenu);
        $this->waitForElementVisible($subMenuItem, Locator::SELECTOR_XPATH);
        $this->_rootElement->find($subMenuItem, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($subMenuSelector, Locator::SELECTOR_XPATH);
    }
}
