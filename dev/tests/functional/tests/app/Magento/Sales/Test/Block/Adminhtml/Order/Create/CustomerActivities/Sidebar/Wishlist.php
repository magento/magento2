<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar;

/**
 * Wish List items block on backend.
 */
class Wishlist extends Sidebar
{
    /**
     * Wish list locator.
     *
     * @var string
     */
    protected $wishlist = '.sidebar-selector';

    /**
     * Wish list items locator.
     *
     * @var string
     */
    protected $wishlistItems = '#sidebar_data_wishlist';

    // @codingStandardsIgnoreStart
    /**
     * Locator for 'Add To Order' checkbox
     *
     * @var string
     */
    protected $addToOrder = '//tr[td[contains(.,"%s")]][td[contains(.,"%d")]][td/span[contains(., "%d")]]//input[contains(@name,"[add_wishlist_item]")]';
    // @codingStandardsIgnoreEnd

    /**
     * Locator for 'Add to order' link for Grouped product
     *
     * @var string
     */
    protected $addToOrderGrouped = '//tr[td[contains(.,"%s")]]//a[contains(@class, "icon-configure")]';

    /**
     * Locator for customer activities block.
     *
     * @var string
     */
    protected $customerActivities = '.customer-current-activity';

    /**
     * No items in wishlist message locator.
     *
     * @var string
     */
    protected $noItemsMessage = '#sidebar_data_wishlist span.no-items';

    /**
     * Locator for item name in wishlist section on backend order page.
     *
     * @var string
     */
    protected $itemName = '#order-sidebar_wishlist tbody .col-item';

    /**
     * Locator for configurable product configuration block.
     *
     * @var string
     */
    protected $configureBlock = "//*[@data-role='modal' and .//*[@id='product_composite_configure']";

    /**
     * Locator for element which contains _show class.
     *
     * @var string
     */
    protected $elementWithShowClass = " and contains(@class,'_show')]";

    /**
     * Get configure block.
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure
     */
    public function getConfigureBlock()
    {
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure::class,
            [
                'element' => $this->_rootElement
                    ->find($this->configureBlock . $this->elementWithShowClass, Locator::SELECTOR_XPATH)
            ]
        );
    }

    /**
     * Get customer activities block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities
     */
    public function getCustomerActivitiesBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities::class,
            [
                'element' => $this->browser->find($this->customerActivities, Locator::SELECTOR_CSS)
            ]
        );
    }

    /**
     * Select wish list in Wish list dropdown.
     *
     * @param string $name
     * @return bool
     */
    public function selectWishlist($name)
    {
        $this->_rootElement->find($this->wishlist, Locator::SELECTOR_CSS, 'select')->setValue($name);
    }

    /**
     * Select item to add to order.
     *
     * @param InjectableFixture $product
     * @param string $qty
     * @return void
     */
    public function selectItemToAddToOrder(InjectableFixture $product, $qty)
    {
        $configureIcon = $this->_rootElement->find(
            sprintf($this->addToOrderGrouped, $product->getName()),
            Locator::SELECTOR_XPATH
        )->isVisible();
        $productCheckoutData = $product->getData('checkout_data');
        if (isset($productCheckoutData['options']) && $configureIcon) {
            $this->_rootElement->find(
                sprintf($this->addToOrderGrouped, $product->getName()),
                Locator::SELECTOR_XPATH
            )->click();
            $this->getConfigureBlock()->clickOk();
        } else {
            $checkBox = $this->_rootElement->find(
                sprintf($this->addToOrder, $product->getName(), $qty, $product->getCheckoutData()['cartItem']['price']),
                Locator::SELECTOR_XPATH,
                'checkbox'
            );
            $checkBox->click();
            $this->_rootElement->click();
            $checkBox->setValue('Yes');
            $this->getCustomerActivitiesBlock()->updateChanges();
        }
    }

    /**
     * Check that backend order wishlist section is empty.
     *
     * @return bool
     */
    public function isSectionEmpty()
    {
        return $this->_rootElement->find($this->noItemsMessage, Locator::SELECTOR_CSS)->isVisible() ? true : false;
    }
}
