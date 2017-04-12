<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Sidebar block on Create Order page on backend.
 */
class Sidebar extends Block
{
    /**
     * Locator for cart section in sidebar on Create Order page on backend.
     *
     * @var string
     */
    protected $orderSidebarCart = '#order-sidebar_cart';

    /**
     * No items in Wish List message locator.
     *
     * @var string
     */
    protected $noItemsMessage = 'span.no-items';

    /**
     * Sidebar "Update changes" button locator.
     *
     * @var string
     */
    protected $updateChangesButton = '[data-ui-id="widget-button-0"]';

    /**
     * Product names in Shopping Cart section in sidebar.
     *
     * @var string
     */
    protected $productNames = '//div[@id="sidebar_data_cart"]//td[@class="col-item"]';

    /**
     * Locator for Shopping Cart section in sidebar.
     *
     * @var string
     */
    protected $cartSection = '//div[@id="sidebar_data_cart"]';

    /**
     * Locator for item row in Shopping Cart section in sidebar.
     *
     * @var string
     */
    protected $itemRowCartSection = '//tr[td[contains(.,"%s")]][td/span[contains(., "%d")]]';

    /**
     * Locator for 'Add to Order' checkbox in Shopping Cart section in sidebar.
     *
     * @var string
     */
    protected $addToOrder = '//input[contains(@name,"[add_cart_item]")]';

    /**
     * Update changes button click.
     *
     * @return void
     */
    public function updateChangesClick()
    {
        $this->_rootElement->find($this->updateChangesButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get names of items from Shopping Cart section.
     *
     * @return array
     */
    public function getCartItemsNames()
    {
        $itemNames = [];
        $items = $this->_rootElement->getElements($this->productNames, Locator::SELECTOR_XPATH);
        foreach ($items as $item) {
            $itemNames[] = $item->getText();
        }

        return $itemNames;
    }

    /**
     * Select item to add to order from Shopping Cart section.
     *
     * @param InjectableFixture $product
     * @return void
     */
    public function selectItemToAddToOrder(InjectableFixture $product)
    {
        $checkBox = $this->_rootElement->find(
            sprintf(
                $this->cartSection . $this->itemRowCartSection . $this->addToOrder,
                $product->getName(),
                $product->getCheckoutData()['cartItem']['price']
            ),
            Locator::SELECTOR_XPATH,
            'checkbox'
        );
        $checkBox->click();
        $this->_rootElement->click();
        $checkBox->setValue('Yes');
    }

    /**
     * Check that Shopping Cart section on Create Order page on backend is empty.
     *
     * @return bool
     */
    public function noItemsInCartCheck()
    {
        return $this->_rootElement->find($this->orderSidebarCart, Locator::SELECTOR_CSS)
            ->find($this->noItemsMessage, Locator::SELECTOR_CSS)->isVisible() ? true : false;
    }
}
