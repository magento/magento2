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
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Uses Sliding Window approach to record request times and enforce limits
 */
class SlidingWindowEnforcer implements BackpressureEnforcerInterface
{
    /**
     * @var RequestLoggerInterface
     */
    private RequestLoggerInterface $logger;

    /**
     * @var LimitConfigManagerInterface
     */
    private LimitConfigManagerInterface $configManager;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param RequestLoggerInterface $logger
     * @param LimitConfigManagerInterface $configManager
     * @param DateTime $dateTime
     */
    public function __construct(
        RequestLoggerInterface $logger,
        LimitConfigManagerInterface $configManager,
        DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->configManager = $configManager;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function enforce(ContextInterface $context): void
    {
        $limit = $this->configManager->readLimit($context);
        $time = $this->dateTime->gmtTimestamp();
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
