<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\Compare;

/**
 * Compare product block on cms page.
 */
class Sidebar extends ListCompare
{
    /**
     * Selector for empty message.
     *
     * @var string
     */
    protected $isEmpty = 'div.empty';

    /**
     * Product name selector.
     *
     * @var string
     */
    protected $productName = 'li.product-item.odd.last strong.product-item-name a';

    /**
     * Selector for "Clear All" button.
     *
     * @var string
     */
    protected $clearAll = '#compare-clear-all';

    /**
     * Get compare products block content.
     *
     * @throws \Exception
     * @return array|string
     */
    public function getProducts()
    {
        try {
            $result = [];
            $rootElement = $this->_rootElement;
            $selector = $this->productName;
            $this->_rootElement->waitUntil(
                function () use ($rootElement, $selector) {
                    return $rootElement->find($selector)->isVisible() ? true : null;
                }
            );
            $elements = $this->_rootElement->getElements($this->productName);
            foreach ($elements as $element) {
                $result[] = $element->getText();
            }
            return $result;
        } catch (\Exception $e) {
            $isEmpty = $this->_rootElement->find($this->isEmpty);
            if ($isEmpty->isVisible()) {
                return $isEmpty->getText();
            } else {
                throw $e;
            }
        }
    }

    /**
     * Click "Clear All" on "My Account" page.
     *
     * @return void
     */
    public function clickClearAll()
    {
        $rootElement = $this->_rootElement;
        $selector = $this->clearAll;
        $this->_rootElement->waitUntil(
            function () use ($rootElement, $selector) {
                return $rootElement->find($selector)->isVisible() ? true : null;
            }
        );
        $this->_rootElement->find($this->clearAll)->click();
        $this->browser->acceptAlert();
    }
}
