<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Ignore values in the config nested array, paths are separated by single slash "/".
     *
     * Example: compiled_config is not set in default mode, and once set it can't be unset
     *
     * @var array
     */
    private $ignoreValues = [
        'cache_types/compiled_config',
    ];

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
            $this->reader = Bootstrap::getObjectManager()->get(\Magento\Framework\App\DeploymentConfig\Reader::class);
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
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        $config = $this->filterIgnoredConfigValues($this->reader->load());
        if ($this->config != $config) {
            $error = "\n\nERROR: deployment configuration is corrupted. The application state is no longer valid.\n"
                . 'Further tests may fail.'
                . " This test failure may be misleading, if you are re-running it on a corrupted application.\n"
                . $test->toString() . "\n";
            $test->fail($error);
        }
    }

    /**
     * Filter ignored config values which are not set by default and appear when tests would change state.
     *
     * Example: compiled_config is not set in default mode, and once set it can't be unset
     *
     * @param array $config
     * @param string $path
     * @return array
     */
    private function filterIgnoredConfigValues(array $config, string $path = '')
    {
        foreach ($config as $configKeyName => $configValue) {
            $newPath = !empty($path) ?  $path . '/' . $configKeyName : $configKeyName;
            if (is_array($configValue)) {
                $config[$configKeyName] = $this->filterIgnoredConfigValues($configValue, $newPath);
            } else {
                if (array_key_exists($newPath, array_flip($this->ignoreValues))) {
                    unset($config[$configKeyName]);
                }
            }
        }
        return $config;
    }
}
