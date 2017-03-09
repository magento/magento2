<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;

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
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Preparing step properties.
     *
     * @param OrderInjectable $order
     */
    public function __construct(OrderInjectable $order, FixtureFactory $fixtureFactory)
    {
        $this->order = $order;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create order.
     *
     * @return array
     */
    public function run()
    {
        $this->order->persist();
        $products = $this->order->getEntityId()['products'];
        $cart['data']['items'] = ['products' => $products];

        return [
            'products' => $products,
            'order' => $this->order,
            'cart' => $this->fixtureFactory->createByCode('cart', $cart)
        ];
    }
}
