<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Mtf\Client\Element;

/**
 * Product details tab.
 */
class ProductDetails extends \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab
{
    /**
     * Locator for "Description" field.
     */
    protected $description = '[name$="[description]"]';

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
            $this->scrollToCategory();
            $this->_fill([$data['category_ids']], $element);
            unset($data['category_ids']);
        }
        $this->_fill($data, $element);

        return $this;
    }

    /**
     * Scroll  page to "Categories" field.
     */
    protected function scrollToCategory()
    {
        $this->_rootElement->find($this->description)->click();
    }
}
