<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Analytics\Cron;

use Magento\Cron\Model\Config as CronConfig;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CronGroupConfigTest extends TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testAnalyticsJobsAreInAnalyticsGroup(): void
    {
        /** @var CronConfig $cronConfig */
        $cronConfig = Bootstrap::getObjectManager()->get(CronConfig::class);
        $jobsByGroup = $cronConfig->getJobs();

        $this->assertArrayHasKey('analytics', $jobsByGroup, 'Cron group "analytics" should exist');

        $analyticsJobs = $jobsByGroup['analytics'];
        $this->assertArrayHasKey('analytics_subscribe', $analyticsJobs);
        $this->assertArrayHasKey('analytics_update', $analyticsJobs);
        $this->assertArrayHasKey('analytics_collect_data', $analyticsJobs);
    }
}
