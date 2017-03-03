<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\TestStep\CancelOrderStep;
use Magento\Sales\Test\TestStep\DenyPaymentStep;
use Magento\Sales\Test\TestStep\UnholdOrderStep;

/**
 * Rollback step for Signifyd scenarios.
 */
class SignifydCancelOrderStep implements TestStepInterface
{
    /**
     * Order index page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * Order fixture.
     *
     * @var OrderInjectable
     */
    private $orderInjectable;

    /**
     * Order View page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Cancel order step.
     *
     * @var CancelOrderStep
     */
    private $cancelOrderStep;

    /**
     * Deny order step.
     *
     * @var DenyPaymentStep
     */
    private $denyPaymentStep;

    /**
     * Unhold order step.
     *
     * @var UnholdOrderStep
     */
    private $unholdOrderStep;

    /**
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $orderInjectable
     * @param SalesOrderView $salesOrderView
     * @param CancelOrderStep $cancelOrderStep
     * @param DenyPaymentStep $denyPaymentStep
     * @param UnholdOrderStep $unholdOrderStep
     */
    public function __construct(
        OrderIndex $orderIndex,
        OrderInjectable $orderInjectable,
        SalesOrderView $salesOrderView,
        CancelOrderStep $cancelOrderStep,
        DenyPaymentStep $denyPaymentStep,
        UnholdOrderStep $unholdOrderStep
    ) {
        $this->orderIndex = $orderIndex;
        $this->orderInjectable = $orderInjectable;
        $this->salesOrderView = $salesOrderView;
        $this->cancelOrderStep = $cancelOrderStep;
        $this->denyPaymentStep = $denyPaymentStep;
        $this->unholdOrderStep = $unholdOrderStep;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()
            ->searchAndOpen(['id' => $this->orderInjectable->getId()]);

        switch ($this->salesOrderView->getOrderInfoBlock()->getOrderStatus()) {
            case 'Suspected Fraud':
                $this->denyPaymentStep->run();
                break;
            case 'On Hold':
                $this->unholdOrderStep->run();
                $this->cancelOrderStep->run();
                break;
            case 'Canceled':
                break;
            default:
                $this->cancelOrderStep->run();
        }
    }
}
