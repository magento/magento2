<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Step for create order.
 */
class CreateOrderStep implements TestStepInterface
{
    /**
     * Order.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param OrderInjectable $order
     */
    public function __construct(OrderInjectable $order)
    {
        $this->order = $order;
    }

    /**
     * Create order.
     *
     * @return array
     */
    public function run()
    {
        $this->order->persist();

        return ['products' => $this->order->getEntityId()['products'], 'order' => $this->order];
    }
}
