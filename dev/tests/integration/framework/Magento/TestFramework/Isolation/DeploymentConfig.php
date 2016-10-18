<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Isolation;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\DeploymentConfig\Reader;

/**
 * A listener that watches for integrity of deployment configuration
 */
class DeploymentConfig
{
    /**
     * Deployment configuration reader
     *
     * @var Reader
     */
    private $reader;

    /**
     * Initial value of deployment configuration
     *
     * @var mixed
     */
    private $config;

    /**
     * Memorizes the initial value of configuration reader and the configuration value
     *
     * Assumption: this is done once right before executing very first test suite.
     * It is assumed that deployment configuration is valid at this point
     *
     * @return void
     */
    public function startTestSuite()
    {
        if (null === $this->reader) {
            $this->reader = Bootstrap::getObjectManager()->get('Magento\Framework\App\DeploymentConfig\Reader');
            $this->config = $this->reader->load();
        }
    }

    /**
     * Checks if deployment configuration has been changed by a test
     *
     * Changing deployment configuration violates isolation between tests, so further tests may become broken.
     * To fix this issue, find out why this test changes deployment configuration.
     * If this is intentional, then it must be reverted to the previous state within the test.
     * After that, the application needs to be wiped out and reinstalled.
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @return void
     */
    public function endTest(\PHPUnit_Framework_TestCase $test)
    {
        $config = $this->reader->load();
        if ($this->config != $config) {
            $error = "\n\nERROR: deployment configuration is corrupted. The application state is no longer valid.\n"
                . 'Further tests may fail.'
                . " This test failure may be misleading, if you are re-running it on a corrupted application.\n"
                . $test->toString() . "\n";
            $test->fail($error);
        }
    }
}
