<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Processes file lock flow of config:set command.
 * This processor saves the value of configuration and lock it for editing in Admin interface.
 *
 * {@inheritdoc}
 */
class LockProcessor implements ConfigSetProcessorInterface
{
    /**
     * The factory for prepared value
     *
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * The deployment configuration writer
     *
     * @var DeploymentConfig\Writer
     */
    private $deploymentConfigWriter;

    /**
     * An array manager for different manipulations with arrays
     *
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * The resolver for configuration paths according to source type
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @param PreparedValueFactory $preparedValueFactory The factory for prepared value
     * @param DeploymentConfig\Writer $writer The deployment configuration writer
     * @param ArrayManager $arrayManager An array manager for different manipulations with arrays
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths according to source type
     */
    public function __construct(
        PreparedValueFactory $preparedValueFactory,
        DeploymentConfig\Writer $writer,
        ArrayManager $arrayManager,
        ConfigPathResolver $configPathResolver
    ) {
        $this->preparedValueFactory = $preparedValueFactory;
        $this->deploymentConfigWriter = $writer;
        $this->arrayManager = $arrayManager;
        $this->configPathResolver = $configPathResolver;
    }

    /**
     * Processes lock flow of config:set command.
     * Requires read access to filesystem.
     *
     * {@inheritdoc}
     */
    public function process($path, $value, $scope, $scopeCode)
    {
        try {
            $configPath = $this->configPathResolver->resolve($path, $scope, $scopeCode, System::CONFIG_TYPE);
            $backendModel = $this->preparedValueFactory->create($path, $value, $scope, $scopeCode);

            if ($backendModel instanceof Value) {
                /**
                 * Temporary solution until Magento introduce unified interface
                 * for storing system configuration into database and configuration files.
                 */
                $backendModel->validateBeforeSave();
                $backendModel->beforeSave();

                $value = $backendModel->getValue();

                $backendModel->afterSave();

                /**
                 * Because FS does not support transactions,
                 * we'll write value just after all validations are triggered.
                 */
                $this->deploymentConfigWriter->saveConfig(
                    [ConfigFilePool::APP_ENV => $this->arrayManager->set($configPath, [], $value)],
                    false
                );
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }
    }
}
