<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\Statistics;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Create custom website.
 * 2. Set custom timezone for the website.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Reports > Refresh Statistics.
 * 3. Select all reports.
 * 4. Update statistics.
 * 5. Perform all assertions.
 *
 * @group Reports
 * @ZephyrId MAGETWO-40919
 */
class RefreshReportsStatisticsTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Reports Statistics page.
     *
     * @var Statistics
     */
    protected $reportStatistics;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Inject pages.
     *
     * @param TestStepFactory $testStepFactory
     * @param Statistics $reportStatistics
     * @return void
     */
    public function __inject(
        TestStepFactory $testStepFactory,
        Statistics $reportStatistics
    ) {
        $this->testStepFactory = $testStepFactory;
        $this->reportStatistics = $reportStatistics;
    }

    /**
     * Refresh reports statistics.
     *
     * @param string $action
     * @param string $configData
     * @return void
     */
    public function test($action, $configData)
    {
        // Preconditions
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();

        // Test steps
        $this->reportStatistics->open();
        $this->reportStatistics->getGridBlock()->massaction([], $action, true, 'Select All');
    }
}
