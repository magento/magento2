<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestFramework\Isolation;

use Magento\TestFramework\App\Config;
use Magento\TestFramework\Config as GlobalConfig;
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
     * @var GlobalConfig|null
     */
    private $globalConfig;

    /**
     * @var string|null
     */
    private $globalConfigFile;

    /**
     * @param string|null $globalConfigFile
     * @param GlobalConfig|null $globalConfig
     */
    public function __construct($globalConfigFile = null, ?GlobalConfig $globalConfig = null)
    {
        $this->globalConfigFile = $globalConfigFile;
        $this->globalConfig = $globalConfig;
    }

    /**
     * Clean memorized and cached setting values and restore the global configuration
     *
     * Assumption: this is done once right before executing very first test suite.
     * It is assumed that deployment configuration is valid at this point
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $this->getTestAppConfig()->clean();
        $this->applyGlobalConfig();
    }

    /**
     * Re-apply values from the global configuration file after the in-memory config was cleaned
     *
     * @return void
     */
    private function applyGlobalConfig()
    {
        if (!$this->globalConfigFile) {
            return;
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!is_file($this->globalConfigFile)) {
            return;
        }

        $this->getGlobalConfig()->rewriteAdditionalConfig();
    }

    /**
     * Retrieve Global Config
     *
     * @return GlobalConfig
     */
    private function getGlobalConfig()
    {
        if (!$this->globalConfig) {
            $this->globalConfig = ObjectManager::getInstance()->create(
                GlobalConfig::class,
                ['configPath' => $this->globalConfigFile]
            );
        }

        return $this->globalConfig;
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
}
