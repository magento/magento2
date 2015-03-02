<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\GridPageActions as ParentGridPageActions;
use Magento\Mtf\Client\Locator;

/**
 * Class GridPageAction
 * Catalog manage products block
 */
class GridPageAction extends ParentGridPageActions
{
    /**
     * Product toggle button
     *
     * @var string
     */
    protected $toggleButton = '[data-ui-id=products-list-add-new-product-button-dropdown]';

    /**
     * Product type item
     *
     * @var string
     */
    protected $productItem = '[data-ui-id=products-list-add-new-product-button-item-%productType%]';

    /**
     * Product type list
     *
     * @var string
     */
    protected $typeList = '[data-ui-id=products-list-add-new-product-button-dropdown-menu]';

    /**
     * Add product using split button
     *
     * @param string $productType
     * @return void
     */
    public function addProduct($productType = 'simple')
    {
        $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS)->click();
        $this->_rootElement->find(
            str_replace('%productType%', $productType, $this->productItem),
            Locator::SELECTOR_CSS
        )->click();
    }

    /**
     * Get product list
     *
     * @return array
     */
    public function getTypeList()
    {
        $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS)->click();
        return $this->_rootElement->find(
            $this->typeList,
            Locator::SELECTOR_CSS
        )->getText();
    }
}
