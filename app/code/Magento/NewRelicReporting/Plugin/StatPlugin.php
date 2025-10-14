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
    public const TIMER_NAME_CRON_PREFIX = 'job';

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
     * @param array $args
     * @return array
     * @see \Magento\Cron\Observer\ProcessCronQueueObserver::startProfiling
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeStart(Stat $schedule, ...$args): array
    {
        $timerName = current($args);

        if ($this->isCronJob($timerName)) {
            $this->newRelicWrapper->startBackgroundTransaction();
            $this->newRelicWrapper->setTransactionName(
                sprintf('Cron %s', $timerName)
            );
        }

        return $args;
    }

    /**
     * Before stopping original profiler, close NewRelic transaction
     *
     * @param Stat $schedule
     * @param array $args
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeStop(Stat $schedule, ...$args): array
    {
        $timerName = current($args);

        if ($this->isCronJob($timerName)) {
            $this->newRelicWrapper->endTransaction();
        }

        return $args;
    }

    /**
     * Determines whether provided name is Cron Job
     *
     * @param string $timerName
     * @return bool
     */
    private function isCronJob(string $timerName): bool
    {
        return 0 === strpos($timerName, static::TIMER_NAME_CRON_PREFIX);
    }
}
