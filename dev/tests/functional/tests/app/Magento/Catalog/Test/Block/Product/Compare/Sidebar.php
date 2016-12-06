<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

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
        $modalElement = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create(
            \Magento\Ui\Test\Block\Adminhtml\Modal::class,
            ['element' => $modalElement]
        );
        $modal->acceptAlert();
    }
}
