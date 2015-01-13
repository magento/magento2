<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Tree
 * Categories tree block
 */
class Tree extends Block
{
    /**
     * Locator value for skip category button
     *
     * @var string
     */
    protected $skipCategoryButton = '[data-ui-id$="skip-categories"]';

    /**
     * Select category by its name
     *
     * @param string|CatalogCategory $category
     * @return void
     */
    public function selectCategory($category)
    {
        //TODO Remove this line after old fixture was deleted
        $categoryName = $category instanceof CatalogCategory ? $category->getName() : $category;
        if ($categoryName) {
            $this->_rootElement->find("//a[contains(text(),'{$categoryName}')]", Locator::SELECTOR_XPATH)->click();
        } else {
            $this->skipCategorySelection();
        }
    }

    /**
     * Skip category selection
     *
     * @return void
     */
    protected function skipCategorySelection()
    {
        $this->_rootElement->find($this->skipCategoryButton, Locator::SELECTOR_CSS)->click();
    }
}
