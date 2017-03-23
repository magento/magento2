<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\ListAssociatedProducts;
use Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search\Grid;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;

/**
 * Grouped products tab.
 */
class AssociatedProducts extends Tab
{
    /**
     * 'Add Products to Group' button.
     *
     * @var string
     */
    protected $addNewOption = '[data-index="grouped_products_button"]';

    /**
     * Associated products grid locator.
     *
     * @var string
     */
    protected $productSearchGrid = '.product_form_product_form_grouped_grouped_products_modal';

    /**
     * Associated products list block.
     *
     * @var string
     */
    protected $associatedProductsBlock = '[data-index="associated"]';

    /**
     * Selector for remove button.
     *
     * @var string
     */
    protected $deleteButton = '[data-action="remove_row"]';

    /**
     * Selector for spinner element.
     *
     * @var string
     */
    protected $loadingMask = '[data-role="spinner"]';

    /**
     * Get search grid.
     *
     * @return Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            \Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search\Grid::class,
            ['element' => $this->browser->find($this->productSearchGrid)]
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
            ListAssociatedProducts::class,
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
    public function setFieldsData(array $fields, SimpleElement $element = null)
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
    public function getFieldsData($fields = null, SimpleElement $element = null)
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
