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
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Uses Sliding Window approach to record request times and enforce limits
 */
class SlidingWindowEnforcer implements BackpressureEnforcerInterface
{
    /**
     * @var RequestLoggerFactoryInterface
     */
    private RequestLoggerFactoryInterface $requestLoggerFactory;

    /**
     * @var LimitConfigManagerInterface
     */
    private LimitConfigManagerInterface $configManager;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param RequestLoggerFactoryInterface $requestLoggerFactory
     * @param LimitConfigManagerInterface $configManager
     * @param DateTime $dateTime
     * @param DeploymentConfig $deploymentConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestLoggerFactoryInterface $requestLoggerFactory,
        LimitConfigManagerInterface $configManager,
        DateTime $dateTime,
        DeploymentConfig $deploymentConfig,
        LoggerInterface $logger
    ) {
        $this->requestLoggerFactory = $requestLoggerFactory;
        $this->configManager = $configManager;
        $this->dateTime = $dateTime;
        $this->deploymentConfig = $deploymentConfig;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     *
     * @throws FileSystemException
     */
    public function enforce(ContextInterface $context): void
    {
        try {
            $requestLogger = $this->getRequestLogger();
            $limit = $this->configManager->readLimit($context);
            $time = $this->dateTime->gmtTimestamp();
            $remainder = $time % $limit->getPeriod();
            //Time slot is the ts of the beginning of the period
            $timeSlot = $time - $remainder;

            $count = $requestLogger->incrAndGetFor(
                $context,
                $timeSlot,
                $limit->getPeriod() * 3//keep data for at least last 3 time slots
            );

            if ($count <= $limit->getLimit()) {
                //Try to compare to a % of requests from previous time slot
                $prevCount = $requestLogger->getFor($context, $timeSlot - $limit->getPeriod());
                if ($prevCount != null) {
                    $count += $prevCount * (1 - ($remainder / $limit->getPeriod()));
                }
            }
            if ($count > $limit->getLimit()) {
                throw new BackpressureExceededException();
            }
        } catch (RuntimeException $e) {
            $this->logger->error('Backpressure sliding window not applied. ' . $e->getMessage());
        }
    }

    /**
     * Returns request logger
     *
     * @return RequestLoggerInterface
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getRequestLogger(): RequestLoggerInterface
    {
        return $this->requestLoggerFactory->create(
            (string)$this->deploymentConfig->get(RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER)
        );
    }
}
