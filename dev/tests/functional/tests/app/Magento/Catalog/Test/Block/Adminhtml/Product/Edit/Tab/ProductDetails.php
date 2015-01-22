<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Mtf\Client\Element\SimpleElement;
use Mtf\Client\Locator;

/**
 * Product details tab.
 */
class ProductDetails extends \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab
{
    /**
     * Locator for preceding sibling of category element.
     *
     * @var string
     */
    protected $categoryPrecedingSibling = '//*[@id="attribute-category_ids-container"]/preceding-sibling::div[%d]';

    /**
     * Locator for following sibling of category element.
     *
     * @var string
     */
    protected $categoryFollowingSibling = '//*[@id="attribute-category_ids-container"]/following-sibling::div[%d]';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);

        if (isset($data['category_ids'])) {
            /* Fix browser behavior for click by hidden list result of suggest(category) element */
            $this->scrollToCategory();
            $this->_fill([$data['category_ids']], $element);
            unset($data['category_ids']);
        }
        $this->_fill($data, $element);

        return $this;
    }

    /**
     * Scroll page to "Categories" field.
     *
     * @return void
     */
    protected function scrollToCategory()
    {
        $this->_rootElement->find(sprintf($this->categoryFollowingSibling, 1), Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find(sprintf($this->categoryPrecedingSibling, 2), Locator::SELECTOR_XPATH)->click();
    }
}
