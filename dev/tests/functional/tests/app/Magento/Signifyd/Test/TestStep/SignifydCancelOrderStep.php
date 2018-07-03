<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepFactory;
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
    private $order;

    /**
     * Order View page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @param SalesOrderView $salesOrderView
     * @param TestStepFactory $testStepFactory
     */
    public function __construct(
        OrderIndex $orderIndex,
        OrderInjectable $order,
        SalesOrderView $salesOrderView,
        TestStepFactory $testStepFactory
    ) {
        $this->orderIndex = $orderIndex;
        $this->order = $order;
        $this->salesOrderView = $salesOrderView;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()
            ->searchAndOpen(['id' => $this->order->getId()]);

        switch ($this->salesOrderView->getOrderInfoBlock()->getOrderStatus()) {
            case 'Suspected Fraud':
                $this->getStepInstance(DenyPaymentStep::class)->run();
                break;
            case 'On Hold':
                $this->getStepInstance(UnholdOrderStep::class)->run();
                $this->getStepInstance(CancelOrderStep::class)->run();
                break;
            case 'Canceled':
                break;
            default:
                $this->getStepInstance(CancelOrderStep::class)->run();
        }
    }

    /**
     * Creates test step instance with preset params.
     *
     * @param string $class
     * @return TestStepInterface
     */
    private function getStepInstance($class)
    {
        return $this->testStepFactory->create(
            $class,
            ['order' => $this->order]
        );
    }
}
