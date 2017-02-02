<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
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
     * The deployment configuration reader.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The deployment configuration writer.
     *
     * @var DeploymentConfig\Writer
     */
    private $deploymentConfigWriter;

    /**
     * An array manager for different manipulations with arrays.
     *
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * The resolver for configuration paths according to source type.
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * The manager for system configuration structure.
     *
     * @var Structure
     */
    private $configStructure;

    /**
     * The factory for configuration value objects.
     *
     * @see Value
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @param DeploymentConfig $deploymentConfig The deployment configuration reader
     * @param DeploymentConfig\Writer $writer The deployment configuration writer
     * @param ArrayManager $arrayManager An array manager for different manipulations with arrays
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths according to source type
     * @param Structure $configStructure The manager for system configuration structure
     * @param ValueFactory $configValueFactory The factory for configuration value objects
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        DeploymentConfig\Writer $writer,
        ArrayManager $arrayManager,
        ConfigPathResolver $configPathResolver,
        Structure $configStructure,
        ValueFactory $configValueFactory
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->deploymentConfigWriter = $writer;
        $this->arrayManager = $arrayManager;
        $this->configPathResolver = $configPathResolver;
        $this->configStructure = $configStructure;
        $this->configValueFactory = $configValueFactory;
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
            /** @var Structure\Element\Field $field */
            $field = $this->deploymentConfig->isAvailable()
                ? $this->configStructure->getElement($path)
                : null;
            /** @var Value $backendModel */
            $backendModel = $field && $field->hasBackendModel()
                ? $field->getBackendModel()
                : $this->configValueFactory->create();

            $backendModel->setPath($path);
            $backendModel->setScope($scope);
            $backendModel->setScopeId($scopeCode);
            $backendModel->setValue($value);

            /**
             * Temporary solution until Magento introduce unified interface
             * for storing system configuration into database and configuration files.
             */
            $backendModel->validateBeforeSave();
            $backendModel->beforeSave();

            $this->deploymentConfigWriter->saveConfig(
                [ConfigFilePool::APP_CONFIG => $this->arrayManager->set($configPath, [], $backendModel->getValue())],
                false
            );

            $backendModel->afterSave();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }
    }
}
