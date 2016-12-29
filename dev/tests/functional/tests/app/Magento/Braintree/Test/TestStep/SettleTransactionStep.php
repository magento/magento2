<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Braintree\Test\Fixture\BraintreeSandboxCustomer;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\ObjectManagerFactory;
use Braintree\Gateway;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Settle transaction for Braintree Credit Card.
 */
class SettleTransactionStep implements TestStepInterface
{
    /**
     * Braintree Sandbox customer fixture.
     *
     * @var BraintreeSandboxCustomer
     */
    private $braintreeSandboxCustomer;

    /**
     * Sales order page.
     *
     * @var OrderIndex
     */
    private $salesOrder;

    /**
     * Sales order view page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Order id.
     *
     * @var string
     */
    private $orderId;

    /**
     * @param BraintreeSandboxCustomer $braintreeSandboxCustomer
     * @param OrderIndex $salesOrder
     * @param SalesOrderView $salesOrderView
     * @param string $orderId
     */
    public function __construct(
        BraintreeSandboxCustomer $braintreeSandboxCustomer,
        OrderIndex $salesOrder,
        SalesOrderView $salesOrderView,
        $orderId
    ) {
        $this->braintreeSandboxCustomer = $braintreeSandboxCustomer;
        $this->salesOrder = $salesOrder;
        $this->salesOrderView = $salesOrderView;
        $this->orderId = $orderId;
    }

    /**
     * Settle transaction for Braintree Credit Card.
     *
     * @return void
     */
    public function run()
    {
        $credentials = $this->braintreeSandboxCustomer->getData();
        $gateway = ObjectManagerFactory::getObjectManager()->create(Gateway::class, ['config' => $credentials]);
        $transactionId = $this->getTransactionId();
        $gateway->testing()->settle($transactionId);
    }

    /**
     * Get transaction id.
     *
     * @return string
     */
    private function getTransactionId()
    {
        $this->salesOrder->open();
        $this->salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $this->orderId]);
        $this->salesOrderView->getOrderForm()->openTab('transactions');
        $actualTransactions = $this->salesOrderView->getOrderForm()->getTab('transactions')->getGridBlock()->getIds();

        return current(array_keys($actualTransactions));
    }
}
