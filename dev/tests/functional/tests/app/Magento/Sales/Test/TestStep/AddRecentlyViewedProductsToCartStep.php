<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Add Recently Viewed Products to cart.
 */
class AddRecentlyViewedProductsToCartStep implements TestStepInterface
{
    /**
     * Products fixture.
     *
     * @var array
     */
    protected $products = [];

    /**
     * Order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * @construct
     * @param array $products
     * @param OrderCreateIndex $orderCreateIndex
     */
    public function __construct(array $products, OrderCreateIndex $orderCreateIndex)
    {
        $this->products = $products;
        $this->orderCreateIndex = $orderCreateIndex;
    }

    /**
     * Add Recently Viewed Products to cart.
     *
     * @return void
     */
    public function run()
    {
        $recentlyBlock = $this->orderCreateIndex->getCustomerActivitiesBlock();
        $recentlyBlock->getRecentlyViewedItemsBlock()->addProductsToOrder($this->products);
        $recentlyBlock->updateChanges();
    }
}
