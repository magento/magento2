<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\TestStep\CancelOrderStep;
use Magento\Sales\Test\TestStep\UnholdOrderStep;

class UnholdAndCancelOrderStep implements TestStepInterface
{
    /**
     * Magento order status.
     *
     * @var string
     */
    private $placeOrderStatus;

    /**
     * @var CancelOrderStep
     */
    private $cancelOrderStep;

    /**
     * @var UnholdOrderStep
     */
    private $unholdOrderStep;

    /**
     * @param string $placeOrderStatus
     * @param CancelOrderStep $cancelOrderStep
     * @param UnholdOrderStep $unholdOrderStep
     */
    public function __construct(
        $placeOrderStatus,
        CancelOrderStep $cancelOrderStep,
        UnholdOrderStep $unholdOrderStep
    ) {
        $this->placeOrderStatus = $placeOrderStatus;
        $this->cancelOrderStep = $cancelOrderStep;
        $this->unholdOrderStep = $unholdOrderStep;
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
            $this->unholdOrderStep->run();
        }

        $this->cancelOrderStep->run();
    }
}
