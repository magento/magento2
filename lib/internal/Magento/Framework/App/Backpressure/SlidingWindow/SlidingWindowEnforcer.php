<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\BackpressureExceededException;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\BackpressureEnforcerInterface;

/**
 * Uses Sliding Window approach to record request times and enforce limits.
 */
class SlidingWindowEnforcer implements BackpressureEnforcerInterface
{
    private RequestLoggerInterface $logger;

    private LimitConfigManagerInterface $configManager;

    /**
     * @param RequestLoggerInterface $logger
     * @param LimitConfigManagerInterface $configManager
     */
    public function __construct(RequestLoggerInterface $logger, LimitConfigManagerInterface $configManager)
    {
        $this->logger = $logger;
        $this->configManager = $configManager;
    }

    /**
     * @inheritDoc
     */
    public function enforce(ContextInterface $context): void
    {
        $limit = $this->configManager->readLimit($context);
        $time = time();
        $remainder = $time % $limit->getPeriod();
        //Time slot is the ts of the beginning of the period
        $timeSlot = $time - $remainder;

        $count = $this->logger->incrAndGetFor(
            $context,
            $timeSlot,
            $limit->getPeriod() * 3//keep data for at least last 3 time slots
        );

        if ($count <= $limit->getLimit()) {
            //Try to compare to a % of requests from previous time slot
            $prevCount = $this->logger->getFor($context, $timeSlot - $limit->getPeriod());
            if ($prevCount != null) {
                $count += $prevCount * (1 - ($remainder / $limit->getPeriod()));
            }
        }
        if ($count > $limit->getLimit()) {
            throw new BackpressureExceededException();
        }
    }
}
