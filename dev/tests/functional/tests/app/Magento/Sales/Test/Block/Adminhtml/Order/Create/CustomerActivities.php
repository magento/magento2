<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\LastOrderedItems;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\ProductsInComparison;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\RecentlyComparedProducts;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\RecentlyViewedItems;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\RecentlyViewedProducts;
use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\ShoppingCartItems;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class CustomerActivities
 * Customer's Activities block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerActivities extends Block
{
    /**
     * 'Update Changes' button
     *
     * @var string
     */
    protected $updateChanges = '.actions .action-default.scalable';

    /**
     * Order sidebar reorder css selector
     *
     * @var string
     */
    protected $reorderSidebar = '#order-sidebar_reorder';

    /**
     * Recently Viewed css selector.
     *
     * @var string
     */
    protected $recentlyViewedSidebar = '#sidebar_data_pviewed';

    /**
     * Order sidebar compared css selector
     *
     * @var string
     */
    protected $comparedSidebar = '#order-sidebar_compared';

    /**
     * Order sidebar compared css selector
     *
     * @var string
     */
    protected $recentlyComparedSidebar = '#order-sidebar_pcompared';

    /**
     * Shopping cart sidebar selector
     * Shopping cart sidebar selector
     *
     * @var string
     */
    protected $shoppingCartSidebar = '#order-sidebar_cart';

    // @codingStandardsIgnoreStart
    /**
     * Last sidebar block selector
     *
     * @var string
     */
    protected $lastSidebar = '//*[@class="create-order-sidebar-container"]/div[div[@class="create-order-sidebar-block"]][last()]';
    // @codingStandardsIgnoreEnd

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Get last ordered items block
     *
     * @return LastOrderedItems
     */
    public function getLastOrderedItemsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\LastOrderedItems::class,
            ['element' => $this->_rootElement->find($this->reorderSidebar)]
        );
    }

    /**
     * Get viewed products block.
     *
     * @return RecentlyViewedItems
     */
    public function getRecentlyViewedItemsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\RecentlyViewedItems::class,
            ['element' => $this->_rootElement->find($this->recentlyViewedSidebar)]
        );
    }

    /**
     * Get products in comparison block
     *
     * @return ProductsInComparison
     */
    public function getProductsInComparisonBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\ProductsInComparison::class,
            ['element' => $this->_rootElement->find($this->comparedSidebar)]
        );
    }

    /**
     * Get products in comparison block
     *
     * @return RecentlyComparedProducts
     */
    public function getRecentlyComparedProductsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\RecentlyComparedProducts::class,
            ['element' => $this->_rootElement->find($this->recentlyComparedSidebar)]
        );
    }

    /**
     * Get products in view block
     *
     * @return RecentlyViewedProducts
     */
    public function getRecentlyViewedProductsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\RecentlyViewedProducts::class,
            ['element' => $this->_rootElement->find($this->recentlyViewedSidebar)]
        );
    }

    /**
     * Get shopping Cart items block
     *
     * @return ShoppingCartItems
     */
    public function getShoppingCartItemsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\ShoppingCartItems::class,
            ['element' => $this->_rootElement->find($this->shoppingCartSidebar)]
        );
    }

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Click 'Update Changes' button
     *
     * @return void
     */
    public function updateChanges()
    {
        $this->_rootElement->find($this->lastSidebar, Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find($this->updateChanges)->click();
        $this->getTemplateBlock()->waitLoader();
    }
}
