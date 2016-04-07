<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class UpdateProductsDataStep
 * Fill Product Data
 */
class UpdateProductsDataStep implements TestStepInterface
{
    /**
     * Sales order create index page
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Products list
     *
     * @var array
     */
    protected $products;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $products
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, array $products)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->products = $products;
    }

    /**
     * Fill product data
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->updateProductsData($this->products);
    }
}
