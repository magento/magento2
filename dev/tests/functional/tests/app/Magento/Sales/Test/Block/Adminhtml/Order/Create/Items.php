<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Backend\Test\Block\Template;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

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
     * Locator for Select element with action in Items Ordered grid.
     *
     * @var string
     */
    protected $actionSelect = ".//span[.='%s']//parent::td//following-sibling::td/select";

    /**
     * "No items ordered" message locator.
     *
     * @var string
     */
    protected $emptyTextMessage = '.empty-text';

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
        $this->getTemplateBlock()->waitLoader();
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
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items\ItemProduct::class,
            ['element' => $this->_rootElement->find(sprintf($this->itemProduct, $name), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get "No items ordered" message.
     *
     * @return string
     */
    public function getEmptyTextMessage()
    {
        return $this->_rootElement->find($this->emptyTextMessage, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get all added to order item names.
     *
     * @return array
     */
    public function getItemsNames()
    {
        $itemNames = [];
        $this->getTemplateBlock()->waitLoader();
        $items = $this->_rootElement->getElements($this->productNames, Locator::SELECTOR_XPATH);
        foreach ($items as $item) {
            $itemNames[] = $item->getText();
        }

        return $itemNames;
    }

    /**
     * Select action for item added to order.
     *
     * @param InjectableFixture $product
     * @param string $action
     * @return void
     */
    public function selectItemAction(InjectableFixture $product, $action)
    {
        $this->_rootElement
            ->find(sprintf($this->actionSelect, $product->getName()), Locator::SELECTOR_XPATH, 'select')
            ->setValue($action);
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
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }
}
