<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Model\Logger\Handler;

use Exception;
use Magento\Config\Setup\ConfigOptionsList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Debug as DebugHandler;

/**
 * Enable/disable debug logging based on the store config setting
 */
class Debug extends DebugHandler
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
     * @param string|null $filePath
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        State $state,
        DeploymentConfig $deploymentConfig,
        ?string $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);

        $this->state = $state;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function isHandling(array $record): bool
    {
        if ($this->deploymentConfig->isAvailable()) {
            return parent::isHandling($record) && $this->isLoggingEnabled();
        }

        return parent::isHandling($record);
    }

    /**
     * Check that logging functionality is enabled
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function isLoggingEnabled(): bool
    {
        $configValue = $this->deploymentConfig->get(
            ConfigOptionsList::CONFIG_PATH_DEBUG_LOGGING
        );

        if ($configValue === null) {
            $isEnabled = $this->state->getMode() !== State::MODE_PRODUCTION;
        } else {
            $isEnabled = (bool) $configValue;
        }

        return $isEnabled;
    }
}
