<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Product\Compare;

use Mtf\Client\Element;

/**
 * Class Sidebar
 * Compare product block on cms page
 */
class Sidebar extends ListCompare
{
    /**
     * Selector for empty message
     *
     * @var string
     */
    protected $isEmpty = 'p.empty';

    /**
     * Product name selector
     *
     * @var string
     */
    protected $productName = 'li.item.odd.last strong.name a';

    /**
     * Selector for "Clear All" button
     *
     * @var string
     */
    protected $clearAll = '#compare-clear-all';

    /**
     * Get compare products block content
     *
     * @return array|string
     */
    public function getProducts()
    {
        $result = [];
        $isEmpty = $this->_rootElement->find($this->isEmpty);
        if ($isEmpty->isVisible()) {
            return $isEmpty->getText();
        }
        $elements = $this->_rootElement->find($this->productName)->getElements();
        foreach ($elements as $element) {
            $result[] = $element->getText();
        }
        return $result;
    }

    /**
     * Click "Clear All" on "My Account" page
     *
     * @return void
     */
    public function clickClearAll()
    {
        $this->_rootElement->find($this->clearAll)->click();
        $this->_rootElement->acceptAlert();
    }
}
