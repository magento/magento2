<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Configure products options on backend order.
 */
class ConfigureProductsStep implements TestStepInterface
{
    /**
     * Products fixtures.
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
     * Configure products options on backend order.
     *
     * @return void
     */
    public function run()
    {
        $orderPage = $this->orderCreateIndex;
        foreach ($this->products as $product) {
            $orderPage->getCreateBlock()->getItemsBlock()->getItemProductByName($product->getName())->configure();
            $orderPage->getConfigureProductBlock()->configProduct($product);
        }
        $orderPage->getCreateBlock()->updateItems();
    }
}
