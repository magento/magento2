<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\Model\Logging;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Log error message for log level Logger::DEBUG and when deployment config "dev/debug/debug_logging" is set to true
 */
class Debug extends \Magento\Framework\Logger\Handler\Debug
{
    /**
     * Path to the debug_logging value in the deployment config.
     */
    private const CONFIG_PATH_DEBUG_LOGGING = 'dev/debug/debug_logging';

    /**
     * Path to the debug_logging value in the deployment config.
     */
    private const CONFIG_PATH_DEBUG_LOGGING_EXTENDED = 'dev/debug/debug_extended';

    /**
     * @var string
     */
    protected $fileName = '/var/log/storefront-serach-debug.log';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DriverInterface $filesystem
     * @param DeploymentConfig $deploymentConfig
     * @param string $filePath
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        DeploymentConfig $deploymentConfig,
        $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);

        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function isHandling(array $record)
    {
        return $record['level'] === $this->level && $this->isLoggingEnabled();
    }

    /**
     * Check that logging functionality is enabled.
     *
     * @return bool
     */
    private function isLoggingEnabled(): bool
    {
        return (bool)$this->deploymentConfig->get(self::CONFIG_PATH_DEBUG_LOGGING);
    }

    /**
     * Check that extended logging functionality is enabled.
     *
     * @return bool
     */
    private function isExtendedLoggingEnabled(): bool
    {
        return (bool)$this->deploymentConfig->get(self::CONFIG_PATH_DEBUG_LOGGING_EXTENDED);
    }

    /**
     * Writes extended debug info
     *
     * @param array $record The record metadata
     * @return void
     */
    public function write(array $record)
    {
        if (!isset($record['context']['verbose'])) {
            parent::write($record);
            return;
        }

        if (isset($record['message']) && $this->isExtendedLoggingEnabled()) {
            $record['message'] .= ' Verbose: ';
            $record['message'] .= \is_array($record['context']['verbose'])
                ? \json_encode($record['context']['verbose'])
                : $record['context']['verbose'];
        }
        unset($record['context']['verbose']);
        $record['formatted'] = $this->getFormatter()->format($record);

        parent::write($record);
    }
}
