<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Block\Html;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Topmenu
 * Class top menu navigation block
 */
class Topmenu extends Block
{
    /**
     * Show all available parent categories
     *
     * @var string
     */
    protected $moreParentCategories = '.more.parent';

    /**
     * Link with category name
     *
     * @var string
     */
    protected $category = '//a[span="%s"]';

    /**
     * Submenu with categories
     *
     * @var string
     */
    protected $submenu = '.submenu';

    /**
     * Top Elements of menu
     *
     * @var string
     */
    protected $navigationMenuItems = "/li";

    /**
     * Select category from top menu by name and click on it
     *
     * @param string $categoryName
     * @return void
     */
    public function selectCategoryByName($categoryName)
    {
        $rootElement = $this->_rootElement;
        $category = $this->waitLoadTopMenu($categoryName);
        if ($category[1]) {
            $rootElement->waitUntil(
                function () use ($category) {
                    return $category[0]->isVisible() ? true : null;
                }
            );
        }
        $category[0]->click();
    }

    /**
     * Check is visible category in top menu by name
     *
     * @param string $categoryName
     * @return bool
     */
    public function isCategoryVisible($categoryName)
    {
        return $this->waitLoadTopMenu($categoryName)[0]->isVisible();
    }

    /**
     * Wait for load top menu
     *
     * @param string $categoryName
     * @return array
     */
    protected function waitLoadTopMenu($categoryName)
    {
        $rootElement = $this->_rootElement;
        $moreCategoriesLink = $rootElement->find($this->moreParentCategories);
        $submenu = $moreCategoriesLink->find($this->submenu);
        $category = $rootElement->find(sprintf($this->category, $categoryName), Locator::SELECTOR_XPATH);
        $notFindCategory = !$category->isVisible() && $moreCategoriesLink->isVisible();
        if (!$category->isVisible() && $moreCategoriesLink->isVisible()) {
            $rootElement->waitUntil(
                function () use ($rootElement, $moreCategoriesLink, $submenu) {
                    $rootElement->click();
                    $moreCategoriesLink->click();
                    return $submenu->isVisible() ? true : null;
                }
            );
        }
        return [$category, $notFindCategory];
    }

    /**
     * Check menu items count
     *
     * @param int $number
     * @return bool
     */
    public function assertNavigationMenuItemsCount($number)
    {
        $selector = $this->navigationMenuItems . '[' . ($number + 1) . ']';
        return !$this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->isVisible();
    }
}
