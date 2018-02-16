<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Backend\Test\Block\Template;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml sales order create items block.
 */
class Items extends Block
{
    /**
     * 'Add Products' button.
     *
     * @var string
     */
    protected $addProducts = "//button[span='Add Products']";

    /**
     * Item product.
     *
     * @var string
     */
    protected $itemProduct = '//tr[td//*[normalize-space(text())="%s"]]';

    /**
     * Product names.
     *
     * @var string
     */
    protected $productNames = '//td[@class="col-product"]/span';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Click 'Add Products' button.
     *
     * @return void
     */
    public function clickAddProducts()
    {
        $element = $this->_rootElement;
        $selector = $this->addProducts;
        $this->_rootElement->waitUntil(
            function () use ($element, $selector) {
                $addProductsButton = $element->find($selector, Locator::SELECTOR_XPATH);
                return $addProductsButton->isVisible() ? true : null;
            }
        );
        $this->_rootElement->find($this->addProducts, Locator::SELECTOR_XPATH)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get item product block.
     *
     * @param string $name
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items\ItemProduct
     */
    public function getItemProductByName($name)
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Items\ItemProduct',
            ['element' => $this->_rootElement->find(sprintf($this->itemProduct, $name), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get products data by fields from items ordered grid.
     *
     * @param array $fields
     * @return array
     */
    public function getProductsDataByFields($fields)
    {
        $this->getTemplateBlock()->waitLoader();
        $this->_rootElement->click();
        $products = $this->_rootElement->getElements($this->productNames, Locator::SELECTOR_XPATH);
        $pageData = [];
        foreach ($products as $product) {
            $pageData[] = $this->getItemProductByName($product->getText())->getCheckoutData($fields);
        }

        return $pageData;
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }
}
