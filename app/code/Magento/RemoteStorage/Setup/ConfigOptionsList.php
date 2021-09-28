<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\RemoteStorage\Driver\DriverFactoryPool;
use Magento\RemoteStorage\Driver\DriverPool as RemoteDriverPool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;
use Psr\Log\LoggerInterface;

/**
 * Remote storage options.
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    private const OPTION_REMOTE_STORAGE_DRIVER = 'remote-storage-driver';
    private const CONFIG_PATH__REMOTE_STORAGE_DRIVER = RemoteDriverPool::PATH_DRIVER;
    private const OPTION_REMOTE_STORAGE_PREFIX = 'remote-storage-prefix';
    private const CONFIG_PATH__REMOTE_STORAGE_PREFIX = RemoteDriverPool::PATH_PREFIX;
    private const OPTION_REMOTE_STORAGE_ENDPOINT = 'remote-storage-endpoint';
    private const CONFIG_PATH__REMOTE_STORAGE_ENDPOINT = RemoteDriverPool::PATH_CONFIG . '/endpoint';
    private const OPTION_REMOTE_STORAGE_BUCKET = 'remote-storage-bucket';
    private const CONFIG_PATH__REMOTE_STORAGE_BUCKET = RemoteDriverPool::PATH_CONFIG . '/bucket';
    private const OPTION_REMOTE_STORAGE_REGION = 'remote-storage-region';
    private const CONFIG_PATH__REMOTE_STORAGE_REGION = RemoteDriverPool::PATH_CONFIG . '/region';
    private const OPTION_REMOTE_STORAGE_ACCESS_KEY = 'remote-storage-key';
    private const CONFIG_PATH__REMOTE_STORAGE_ACCESS_KEY = RemoteDriverPool::PATH_CONFIG . '/credentials/key';
    private const OPTION_REMOTE_STORAGE_SECRET_KEY = 'remote-storage-secret';
    private const CONFIG_PATH__REMOTE_STORAGE_SECRET_KEY = RemoteDriverPool::PATH_CONFIG . '/credentials/secret';
    private const OPTION_REMOTE_STORAGE_PATH_STYLE = 'remote-storage-path-style';
    private const CONFIG_PATH__REMOTE_STORAGE_PATH_STYLE = RemoteDriverPool::PATH_CONFIG . '/path-style';

    /**
     * Map of option to config path relations.
     *
     * @var string[]
     */
    private static $map = [
        self::OPTION_REMOTE_STORAGE_PREFIX => self::CONFIG_PATH__REMOTE_STORAGE_PREFIX,
        self::OPTION_REMOTE_STORAGE_ENDPOINT => self::CONFIG_PATH__REMOTE_STORAGE_ENDPOINT,
        self::OPTION_REMOTE_STORAGE_BUCKET => self::CONFIG_PATH__REMOTE_STORAGE_BUCKET,
        self::OPTION_REMOTE_STORAGE_REGION => self::CONFIG_PATH__REMOTE_STORAGE_REGION,
        self::OPTION_REMOTE_STORAGE_ACCESS_KEY => self::CONFIG_PATH__REMOTE_STORAGE_ACCESS_KEY,
        self::OPTION_REMOTE_STORAGE_SECRET_KEY => self::CONFIG_PATH__REMOTE_STORAGE_SECRET_KEY,
        self::OPTION_REMOTE_STORAGE_PATH_STYLE => self::CONFIG_PATH__REMOTE_STORAGE_PATH_STYLE
    ];

    /**
     * @var DriverFactoryPool
     */
    private $driverFactoryPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DriverFactoryPool $driverFactoryPool
     * @param LoggerInterface $logger
     */
    public function __construct(DriverFactoryPool $driverFactoryPool, LoggerInterface $logger)
    {
        $this->driverFactoryPool = $driverFactoryPool;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        return [
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_DRIVER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH__REMOTE_STORAGE_DRIVER,
                'Remote storage driver',
                DriverPool::FILE
            ),
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH__REMOTE_STORAGE_PREFIX,
                'Remote storage prefix',
                ''
            ),
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_ENDPOINT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH__REMOTE_STORAGE_ENDPOINT,
                'Remote storage endpoint'
            ),
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_BUCKET,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH__REMOTE_STORAGE_BUCKET,
                'Remote storage bucket'
            ),
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_REGION,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH__REMOTE_STORAGE_REGION,
                'Remote storage region'
            ),
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_ACCESS_KEY,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                self::CONFIG_PATH__REMOTE_STORAGE_ACCESS_KEY,
                'Remote storage access key',
                ''
            ),
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_SECRET_KEY,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                self::CONFIG_PATH__REMOTE_STORAGE_SECRET_KEY,
                'Remote storage secret key',
                ''
            ),
            new TextConfigOption(
                self::OPTION_REMOTE_STORAGE_PATH_STYLE,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                self::CONFIG_PATH__REMOTE_STORAGE_PATH_STYLE,
                'Remote storage path style',
                '0'
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig): array
    {
        $driver = $options[self::OPTION_REMOTE_STORAGE_DRIVER] ?? DriverPool::FILE;

        if ($driver === DriverPool::FILE) {
            $configData = new ConfigData(ConfigFilePool::APP_ENV);
            $configData->setOverrideWhenSave(true);
            $configData->set(self::CONFIG_PATH__REMOTE_STORAGE_DRIVER, $driver);
        } else {
            $configData = $this->createConfigData($driver, $options);
        }

        return [$configData];
    }

    /**
     * @inheritDoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig): array
    {
        // deployment configuration existence determines readiness of object manager to resolve remote storage drivers
        $isDeploymentConfigExists = (bool) $deploymentConfig->getConfigData();

        if (!$isDeploymentConfigExists) {
            return [];
        }

        $driver = $options[self::OPTION_REMOTE_STORAGE_DRIVER] ?? DriverPool::FILE;

        if ($driver === DriverPool::FILE) {
            return [];
        }

        $errors = [];

        if (empty($options[self::OPTION_REMOTE_STORAGE_REGION])) {
            $errors[] = 'Region is required';
        }

        if (empty($options[self::OPTION_REMOTE_STORAGE_BUCKET])) {
            $errors[] = 'Bucket is required';
        }

        if (!$errors) {
            $configData = $this->createConfigData($driver, $options);

            try {
                $this->driverFactoryPool->get($driver)->createConfigured(
                    (array)$configData->getData()['remote_storage']['config'],
                    (string)$options[self::OPTION_REMOTE_STORAGE_PREFIX]
                )->test();
            } catch (LocalizedException $exception) {
                $message = $exception->getMessage();

                $this->logger->critical($message);

                $errors[] = 'Adapter error: ' . $message;
            }
        }

        return $errors;
    }

    /**
     * Creates pre-configured config data object.
     *
     * @param string $driver
     * @param array $options
     * @return ConfigData
     */
    private function createConfigData(string $driver, array $options): ConfigData
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        $configData->setOverrideWhenSave(true);
        $configData->set(self::CONFIG_PATH__REMOTE_STORAGE_DRIVER, $driver);

        foreach (self::$map as $option => $configPath) {
            if (!empty($options[$option])) {
                $configData->set($configPath, $options[$option]);
            }
        }

        return $configData;
    }
}
