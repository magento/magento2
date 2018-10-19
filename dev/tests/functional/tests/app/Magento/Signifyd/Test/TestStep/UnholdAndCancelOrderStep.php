<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\TestStep\CancelOrderStep;
use Magento\Sales\Test\TestStep\UnholdOrderStep;

/**
 * Unhold and cancel order.
 */
class UnholdAndCancelOrderStep implements TestStepInterface
{
    /**
     * Magento order status.
     *
     * @var string
     */
    private $placeOrderStatus;

    /**
     * Order fixture.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * @param string $placeOrderStatus
     * @param OrderInjectable $order
     * @param TestStepFactory $testStepFactory
     */
    public function __construct(
        $placeOrderStatus,
        OrderInjectable $order,
        TestStepFactory $testStepFactory
    ) {
        $this->placeOrderStatus = $placeOrderStatus;
        $this->order = $order;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Cancel order step.
     *
     * If order was held - unhold and then cancel the order.
     *
     * @return void
     */
    public function run()
    {
        if ($this->placeOrderStatus === 'On Hold') {
            $this->getStepInstance(UnholdOrderStep::class)->run();
        }

        $this->getStepInstance(CancelOrderStep::class)->run();
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
