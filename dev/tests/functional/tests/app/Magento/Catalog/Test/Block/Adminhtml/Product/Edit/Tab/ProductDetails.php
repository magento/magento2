<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

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
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
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
