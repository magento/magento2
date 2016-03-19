<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\ListAssociatedProducts;
use Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search\Grid;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;

/**
 * Grouped products tab.
 */
class AssociatedProducts extends Tab
{
    /**
     * 'Create New Option' button.
     *
     * @var string
     */
    protected $addNewOption = '#grouped-product-container>button';

    /**
     * Associated products grid locator.
     *
     * @var string
     */
    protected $productSearchGrid = './/*[@data-role="modal"][.//*[@data-role="add-product-dialog"]]';

    /**
     * Associated products list block.
     *
     * @var string
     */
    protected $associatedProductsBlock = '[data-role=grouped-product-grid]';

    /**
     * Selector for delete button.
     *
     * @var string
     */
    protected $deleteButton = '[data-role="delete"]';

    /**
     * Selector for loading mask element.
     *
     * @var string
     */
    protected $loadingMask = '.loading-mask';

    /**
     * Get search grid.
     *
     * @return Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search\Grid',
            ['element' => $this->browser->find($this->productSearchGrid, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get associated products list block.
     *
     * @return ListAssociatedProducts
     */
    protected function getListAssociatedProductsBlock()
    {
        return $this->blockFactory->create(
            'Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\ListAssociatedProducts',
            ['element' => $this->_rootElement->find($this->associatedProductsBlock)]
        );
    }

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        if (isset($fields['associated'])) {
            $options = $this->_rootElement->getElements($this->deleteButton);
            if (count($options)) {
                foreach (array_reverse($options) as $option) {
                    $option->click();
                }
            }
            foreach ($fields['associated']['value']['assigned_products'] as $key => $groupedProduct) {
                $element->find($this->addNewOption)->click();
                $searchBlock = $this->getSearchGridBlock();
                $this->waitLoaderNotVisible();
                $searchBlock->searchAndSelect(['name' => $groupedProduct['name']]);
                $searchBlock->addProducts();
                $this->getListAssociatedProductsBlock()->fillProductOptions($groupedProduct, ($key + 1));
            }
        }
        return $this;
    }

    /**
     * Get data to fields on group tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $newFields = [];
        if (isset($fields['associated'])) {
            foreach ($fields['associated']['value']['assigned_products'] as $key => $groupedProduct) {
                $newFields['associated']['assigned_products'][$key] = $this->getListAssociatedProductsBlock()
                    ->getProductOptions($groupedProduct, ($key + 1));
            }
        }
        return $newFields;
    }

    /**
     * Wait until loader is not visible.
     *
     * return void
     */
    protected function waitLoaderNotVisible()
    {
        $browser = $this->browser;
        $selector = $this->loadingMask;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() === false ? true : null;
            }
        );
    }
}
