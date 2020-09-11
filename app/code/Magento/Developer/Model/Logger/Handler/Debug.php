<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\Logger\Handler;

use Magento\Config\Setup\ConfigOptionsList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Enable/disable debug logging based on the store config setting
 */
class Debug extends \Magento\Framework\Logger\Handler\Debug
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DriverInterface $filesystem
     * @param State $state
     * @param DeploymentConfig $deploymentConfig
     * @param string $filePath
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        State $state,
        DeploymentConfig $deploymentConfig,
        $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);

        $this->state = $state;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function isHandling(array $record)
    {
        if ($this->deploymentConfig->isAvailable()) {
            return
                parent::isHandling($record)
                && $this->isLoggingEnabled();
        }

        return parent::isHandling($record);
    }

    /**
     * Check that logging functionality is enabled.
     *
     * @return bool
     */
    private function isLoggingEnabled(): bool
    {
        $configValue = $this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_DEBUG_LOGGING);
        if ($configValue === null) {
            $isEnabled = $this->state->getMode() !== State::MODE_PRODUCTION;
        } else {
            $isEnabled = (bool)$configValue;
        }
        return $isEnabled;
    }
}
