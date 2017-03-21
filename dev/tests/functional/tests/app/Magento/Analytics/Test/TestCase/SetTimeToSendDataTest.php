<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Steps:
 * 1. Login as admin user in backend
 * 2. Navigate to menu Stores>Configuration>General>Advanced Reporting->General
 * 3. Set Option "Time of day to send data"
 * 4. Click "Save Config"
 * 5. Perform assertions
 *
 * @ZephyrId MAGETWO-66464
 */
class SetTimeToSendDataTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * @var array
     */
    private $configData;

    /**
     * @param ConfigAnalytics $configAnalytics
     * @param TestStepFactory $testStepFactory
     * @param $hh
     * @param $mm
     * @param string $vertical
     * @param array $configData
     */
    public function test(
        ConfigAnalytics $configAnalytics,
        TestStepFactory $testStepFactory,
        $hh,
        $mm,
        $vertical,
        $configData
    ) {
        $this->configData = $configData;
        $testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        $configAnalytics->open();
        $configAnalytics->getAnalyticsForm()->setAnalyticsVertical($vertical);
        $configAnalytics->getAnalyticsForm()->setTimeOfDayToSendData($hh, $mm);
        $configAnalytics->getAnalyticsForm()->saveConfig();
    }


    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
