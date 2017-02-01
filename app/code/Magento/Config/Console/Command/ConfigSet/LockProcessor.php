<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\ArrayManager;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Processes file lock flow of config:set command.
 * This processor saves the value of configuration and lock it for editing in Admin interface.
 *
 * {@inheritdoc}
 */
class LockProcessor implements ConfigSetProcessorInterface
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var DeploymentConfig\Writer
     */
    private $deploymentConfigWriter;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @var Structure
     */
    private $configStructure;

    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param DeploymentConfig\Writer $writer
     * @param ArrayManager $arrayManager
     * @param ConfigPathResolver $configPathResolver
     * @param Structure $configStructure
     * @param ValueFactory $configValueFactory
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
    public function process(InputInterface $input)
    {
        $path = $input->getArgument(ConfigSetCommand::ARG_PATH);
        $value = $input->getArgument(ConfigSetCommand::ARG_VALUE);
        $scope = $input->getOption(ConfigSetCommand::OPTION_SCOPE);
        $scopeCode = $input->getOption(ConfigSetCommand::OPTION_SCOPE_CODE);
        $configPath = $this->configPathResolver->resolve($path, $scope, $scopeCode, System::CONFIG_TYPE);

        /** @var Field $field */
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

        try {
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
        } catch (FileSystemException $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }
    }
}
