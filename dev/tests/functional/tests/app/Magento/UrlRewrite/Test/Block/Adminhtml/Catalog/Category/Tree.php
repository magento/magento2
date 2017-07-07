<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Categories tree block.
 */
class Tree extends Block
{
    /**
     * Locator value for skip category button.
     *
     * @var string
     */
    protected $skipCategoryButton = '[data-ui-id$="skip-categories"]';

    /**
     * Select category by its name.
     *
     * @param Category|null $category
     * @return void
     */
    public function selectCategory($category)
    {
        if ($category != null && $category->hasData('name')) {
            $this->_rootElement->find(
                "//a[contains(text(),'{$category->getName()}')]",
                Locator::SELECTOR_XPATH
            )->click();
        } else {
            $this->skipCategorySelection();
        }
    }

    /**
     * Skip category selection.
     *
     * @return void
     */
    protected function skipCategorySelection()
    {
        $this->_rootElement->find($this->skipCategoryButton, Locator::SELECTOR_CSS)->click();
    }
}
