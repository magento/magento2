<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cron\Cron;

use Magento\Config\App\Config\Type\System;
use Magento\Cron\Model\DeadlockRetrierInterface;
use Magento\Cron\Model\ResourceModel\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Cron\Observer\ProcessCronQueueObserver;
use Magento\Framework\App\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CleanOldJobs
{
    /**
     * This docblock provides no new information
     *
     * @param Config $config
     * @param DateTime $dateTime
     * @param DeadlockRetrierInterface $retrier
     * @param ScheduleFactory $scheduleFactory
     */
    public function __construct(
        private readonly Config $config,
        private readonly DateTime $dateTime,
        private readonly DeadlockRetrierInterface $retrier,
        private readonly ScheduleFactory $scheduleFactory,
    ) {
    }

    /**
     * Run the 'clean_cron_schedule' cronjob
     *
     * @return void
     */
    public function execute(): void
    {
        $fullConfig = $this->config->get(System::CONFIG_TYPE);
        $maxLifetime = 0;

        array_walk_recursive(
            $fullConfig,
            static function ($value, $key) use (&$maxLifetime) {
                if ($key === ProcessCronQueueObserver::XML_PATH_HISTORY_SUCCESS
                    || $key === ProcessCronQueueObserver::XML_PATH_HISTORY_FAILURE
                ) {
                    $maxLifetime = max($maxLifetime, (int) $value);
                }
            }
        );

        if ($maxLifetime === 0) {
            // Something has gone wrong. Why are there no configuration values?
            // Drop out now to avoid doing any damage to this already-broken installation.
            return;
        }

        // The value stored in XML is in minutes, we want seconds.
        $maxLifetime *= 60;

        // Add one day to avoid removing items which are near their natural expiry anyway.
        $maxLifetime += 86400;

        /** @var Schedule $scheduleResource */
        $scheduleResource = $this->scheduleFactory->create()->getResource();

        $currentTime = $this->dateTime->gmtTimestamp();
        $deleteBefore = $scheduleResource->getConnection()->formatDate($currentTime - $maxLifetime);

        $this->retrier->execute(
            function () use ($scheduleResource, $deleteBefore) {
                $scheduleResource->getConnection()->delete(
                    $scheduleResource->getTable('cron_schedule'),
                    [
                        'scheduled_at < ?' => $deleteBefore,
                    ]
                );
            },
            $scheduleResource->getConnection()
        );
    }
}
