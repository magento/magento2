<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

use Magento\Mtf\Client\Element\MultisuggestElement;
use Magento\Mtf\Client\ElementInterface;

/**
 * Typified element class for category element.
 */
class CategoryIds extends MultisuggestElement
{
    /**
     * Selector item of search result.
     *
     * @var string
     */
    protected $resultItem = './/label[contains(@class, "admin__action-multiselect-label")]/span[text() = "%s"]';

    /**
     * Locator for new category button.
     *
     * @var string
     */
    protected $newCategory = '[data-index="create_category_button"]';

    /**
     * Click on searched category item.
     *
     * @param ElementInterface $searchedItem
     * @return void
     */
    protected function clickOnSearchedItem(ElementInterface $searchedItem)
    {
        $searchedItem->hover();
        $this->getContext()->find($this->newCategory)->hover();
        parent::clickOnSearchedItem($searchedItem);
    }
}
