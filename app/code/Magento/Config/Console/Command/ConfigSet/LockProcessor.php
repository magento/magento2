<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
 * This processor saves the value of configuration into app/etc/env.php
 * and locks it for editing in Admin interface.
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
     * @var string
     */
    private $target;

    /**
     * @param PreparedValueFactory $preparedValueFactory The factory for prepared value
     * @param DeploymentConfig\Writer $writer The deployment configuration writer
     * @param ArrayManager $arrayManager An array manager for different manipulations with arrays
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths according to source type
     * @param string $target
     */
    public function __construct(
        PreparedValueFactory $preparedValueFactory,
        DeploymentConfig\Writer $writer,
        ArrayManager $arrayManager,
        ConfigPathResolver $configPathResolver,
        $target = ConfigFilePool::APP_ENV
    ) {
        $this->preparedValueFactory = $preparedValueFactory;
        $this->deploymentConfigWriter = $writer;
        $this->arrayManager = $arrayManager;
        $this->configPathResolver = $configPathResolver;
        $this->target = $target;
    }

    /**
     * Processes lock flow of config:set command.
     *
     * Requires read access to filesystem.
     *
     * @param string $path The configuration path in format group/section/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope
     * @param string $scopeCode The scope code
     * @return void
     * @throws CouldNotSaveException An exception on processing error
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

                /**
                 * Capture the value before beforeSave() is called, since backend models
                 * may transform the value in beforeSave() for database serialization
                 * (e.g. converting a string to an array). The env.php lock storage
                 * requires the validated scalar value, not the DB-serialized form.
                 */
                $value = $backendModel->getValue();

                $backendModel->beforeSave();
                $backendModel->afterSave();

                /**
                 * Because FS does not support transactions,
                 * we'll write value just after all validations are triggered.
                 */
                $this->deploymentConfigWriter->saveConfig(
                    [$this->target => $this->arrayManager->set($configPath, [], $value)],
                    false
                );
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }
    }
}
