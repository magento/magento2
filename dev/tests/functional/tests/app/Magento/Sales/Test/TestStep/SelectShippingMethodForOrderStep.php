<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class SelectShippingMethodForOrderStep.
 * Select Shipping data
 */
class SelectShippingMethodForOrderStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Shipping.
     *
     * @var array
     */
    protected $shipping;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $shipping
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, array $shipping = null)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->shipping = $shipping;
    }

    /**
     * Fill Shipping Data.
     *
     * @return array
     */
    public function run()
    {
        if ($this->shipping['shipping_service'] !== null) {
            $this->orderCreateIndex->getCreateBlock()->selectShippingMethod($this->shipping);
        }

        return ['shipping' => $this->shipping];
    }
}
