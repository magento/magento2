<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestStep\TestStepInterface;

/**
 * Submit Order step.
 */
class SubmitOrderStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Sales order view.
     *
     * @var OrderView
     */
    protected $orderView;

    /**
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param OrderView $orderView
     * @param FixtureFactory $fixtureFactory
     * @param CustomerInjectable $customer
     * @param AddressInjectable $billingAddress
     * @param \Mtf\Fixture\FixtureInterface[] $products
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        OrderView $orderView,
        FixtureFactory $fixtureFactory,
        CustomerInjectable $customer,
        AddressInjectable $billingAddress,
        array $products
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->orderView = $orderView;
        $this->fixtureFactory = $fixtureFactory;
        $this->customer = $customer;
        $this->billingAddress = $billingAddress;
        $this->products = $products;
    }

    /**
     * Fill Sales Data.
     *
     * @return array
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->submitOrder();
        $this->orderView->getMessagesBlock()->waitSuccessMessage();
        $orderId = trim($this->orderView->getTitleBlock()->getTitle(), '#');
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'data' => [
                    'id' => $orderId,
                    'customer_id' => ['customer' => $this->customer],
                    'entity_id' => ['products' => $this->products],
                    'billing_address_id' => ['billingAddress' => $this->billingAddress],
                ]
            ]
        );

        return ['orderId' => $orderId, 'order' => $order];
    }
}
