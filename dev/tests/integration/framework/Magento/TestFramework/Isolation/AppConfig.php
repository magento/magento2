<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Isolation;

use Magento\TestFramework\App\Config;
use Magento\TestFramework\ObjectManager;

/**
 * A listener that watches for integrity of app configuration
 */
class AppConfig
{
    /**
     * @var Config
     */
    private $testAppConfig;

    /**
     * Clean memorized and cached setting values
     *
     * Assumption: this is done once right before executing very first test suite.
     * It is assumed that deployment configuration is valid at this point
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \PHPUnit_Framework_TestCase $test
     * @return void
     */
    public function startTest(\PHPUnit_Framework_TestCase $test)
    {
        $this->getTestAppConfig()->clean();
    }

    /**
     * Retrieve Test App Config
     *
     * @return Config
     */
    private function getTestAppConfig()
    {
        if (!$this->testAppConfig) {
            $this->testAppConfig = ObjectManager::getInstance()->get(Config::class);
        }

        return $this->testAppConfig;
    }

    /**Clean memorized and cached setting values
     * Checks if deployment configuration has been changed by a test
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \PHPUnit_Framework_TestCase $test
     * @return void
     */
    public function endTest(\PHPUnit_Framework_TestCase $test)
    {
        $this->getTestAppConfig()->clean();
    }
}
