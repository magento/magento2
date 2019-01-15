<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

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
    private $orderCreateIndex;

    /**
     * Sales order view.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    private $customer;

    /**
     * Billing Address fixture.
     *
     * @var Address
     */
    private $billingAddress;

    /**
     * Products fixtures.
     *
     * @var array|\Magento\Mtf\Fixture\FixtureInterface[]
     */
    private $products;

    /**
     * Fixture OrderInjectable.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param SalesOrderView $salesOrderView
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @param \Magento\Mtf\Fixture\FixtureInterface[] $products
     * @param Address|null $billingAddress
     * @param OrderInjectable|null $order
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        SalesOrderView $salesOrderView,
        FixtureFactory $fixtureFactory,
        Customer $customer,
        array $products,
        Address $billingAddress = null,
        OrderInjectable $order = null
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->salesOrderView = $salesOrderView;
        $this->fixtureFactory = $fixtureFactory;
        $this->customer = $customer;
        $this->billingAddress = $billingAddress;
        $this->products = $products;
        $this->order = $order;
    }

    /**
     * Fill Sales Data.
     *
     * @return array
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->submitOrder();
        $this->salesOrderView->getMessagesBlock()->waitSuccessMessage();
        $orderId = trim($this->salesOrderView->getTitleBlock()->getTitle(), '#');
        $data = [
            'id' => $orderId,
            'customer_id' => ['customer' => $this->customer],
            'entity_id' => ['products' => $this->products],
            'billing_address_id' => ['billingAddress' => $this->billingAddress],
        ];
        $orderData = $this->order !== null ? $this->order->getData() : [];
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            ['data' => array_merge($data, $orderData)]
        );

        return ['orderId' => $orderId, 'order' => $order];
    }
}
