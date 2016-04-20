<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

use Magento\Mtf\Client\Element\MultisuggestElement;
use Magento\Mtf\Client\Locator;

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
     * Select searched item.
     *
     * @param string $value
     * @return void
     */
    protected function selectSearchedItem($value)
    {
        $this->keys([$value]);
        $searchedItem = $this->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH);
        if ($searchedItem->isVisible()) {
            try {
                $searchedItem->click();
            } catch (\Exception $e) {
                // In parallel run on windows change the focus is lost on element
                // that causes disappearing of category suggest list.
            }
        }
    }
}
