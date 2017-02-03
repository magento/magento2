<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Submit Order with vault step.
 */
class SubmitOrderWithVaultStep implements TestStepInterface
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
     * @var array
     */
    private $vault;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param SalesOrderView $salesOrderView
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @param \Magento\Mtf\Fixture\FixtureInterface[] $products
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $vault
     * @param Address|null $billingAddress
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        SalesOrderView $salesOrderView,
        FixtureFactory $fixtureFactory,
        Customer $customer,
        array $products,
        array $vault,
        Address $billingAddress = null
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->salesOrderView = $salesOrderView;
        $this->fixtureFactory = $fixtureFactory;
        $this->customer = $customer;
        $this->billingAddress = $billingAddress;
        $this->products = $products;
        $this->vault = $vault;
    }

    /**
     * Fill Sales Data.
     *
     * @return array
     */
    public function run()
    {
        $block = $this->orderCreateIndex->getCreateBlock();
        $block->selectPaymentMethod($this->vault);
        $block->selectVaultToken('token_switcher_' . $this->vault['method']);
        $this->orderCreateIndex->getCreateBlock()->submitOrder();
        $this->salesOrderView->getMessagesBlock()->waitSuccessMessage();
        $orderId = trim($this->salesOrderView->getTitleBlock()->getTitle(), '#');
        $data = [
            'id' => $orderId,
            'customer_id' => ['customer' => $this->customer],
            'entity_id' => ['products' => $this->products],
            'billing_address_id' => ['billingAddress' => $this->billingAddress],
        ];
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            ['data' => $data]
        );

        return ['orderId' => $orderId, 'order' => $order];
    }
}
