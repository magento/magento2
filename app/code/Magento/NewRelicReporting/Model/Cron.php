<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

use Magento\NewRelicReporting\Model\Cron\ReportCounts;
use Magento\NewRelicReporting\Model\Cron\ReportModulesInfo;
use Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron;

/**
 * Class \Magento\NewRelicReporting\Model\Cron
 *
 * @since 2.0.0
 */
class Cron
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var ReportModulesInfo
     * @since 2.0.0
     */
    protected $reportModulesInfo;

    /**
     * @var ReportCounts
     * @since 2.0.0
     */
    protected $reportCounts;

    /**
     * @var ReportNewRelicCron
     * @since 2.0.0
     */
    protected $reportNewRelicCron;

    /**
     * Constructor
     *
     * @param Config $config
     * @param ReportModulesInfo $reportModulesInfo
     * @param ReportCounts $reportCounts
     * @param ReportNewRelicCron $reportNewRelicCron
     * @since 2.0.0
     */
    public function __construct(
        Config $config,
        ReportModulesInfo $reportModulesInfo,
        ReportCounts $reportCounts,
        ReportNewRelicCron $reportNewRelicCron
    ) {
        $this->config = $config;
        $this->reportModulesInfo = $reportModulesInfo;
        $this->reportCounts = $reportCounts;
        $this->reportNewRelicCron = $reportNewRelicCron;
    }

    /**
     * The method run by the cron that fires all required events.
     *
     * @return \Magento\NewRelicReporting\Model\Cron
     * @since 2.0.0
     */
    public function runCron()
    {
        if ($this->config->isCronEnabled()) {
            $this->reportNewRelicCron->report();
            $this->reportModulesInfo->report();
            $this->reportCounts->report();
        }

        return $this;
    }
}
