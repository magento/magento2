<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

use Magento\NewRelicReporting\Model\Cron\ReportModulesInfo;
use Magento\NewRelicReporting\Model\Cron\ReportCounts;
use Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron;

class Cron
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ReportModulesInfo
     */
    protected $reportModulesInfo;

    /**
     * @var ReportCounts
     */
    protected $reportCounts;

    /**
     * @var ReportNewRelicCron
     */
    protected $reportNewRelicCron;

    /**
     * Constructor
     * 
     * @param Config $config
     * @param ReportModulesInfo $reportModulesInfo
     * @param ReportCounts $reportCounts
     * @param ReportNewRelicCron $reportNewRelicCron
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
