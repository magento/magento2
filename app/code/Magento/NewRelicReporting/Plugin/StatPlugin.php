<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Plugin;

use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Psr\Log\LoggerInterface;

/**
 * Class StatPlugin handles single Cron Jobs transaction names
 */
class StatPlugin
{
    const TIMER_NAME_CRON_PREFIX = 'job';
    /**
     * @var Config
     */
    private $config;

    /**
     * @var NewRelicWrapper
     */
    private $newRelicWrapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param NewRelicWrapper $newRelicWrapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        NewRelicWrapper $newRelicWrapper,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->newRelicWrapper = $newRelicWrapper;
        $this->logger = $logger;
    }

    /**
     * Before running original profiler, register NewRelic transaction
     *
     * @param Stat $schedule
     * @param array ...$args
     * @return array
     * @see \Magento\Cron\Observer\ProcessCronQueueObserver::startProfiling
     */
    public function beforeStart(Stat $schedule, ...$args): array
    {
        $timerName = current($args);

        if (0 === strpos($timerName, static::TIMER_NAME_CRON_PREFIX)) {
            $this->newRelicWrapper->setTransactionName(
                sprintf('Cron %s', $timerName)
            );
        }

        return $args;
    }

    /**
     * @param Stat $schedule
     * @param array ...$args
     * @return array
     */
    public function beforeStop(Stat $schedule, ...$args): array
    {
        $this->newRelicWrapper->endTransaction();

        return $args;
    }
}
