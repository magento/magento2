<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\Value;

/**
 * Processes default flow of config:set command.
 * This processor saves the value of configuration into database.
 *
 * {@inheritdoc}
 * @api
 */
class DefaultProcessor implements ConfigSetProcessorInterface
{
    /**
     * The deployment configuration reader.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The resolver for configuration paths according to source type.
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * The factory for prepared value.
     *
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * @param PreparedValueFactory $preparedValueFactory The factory for prepared value
     * @param DeploymentConfig $deploymentConfig The deployment configuration reader
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths according to source type
     */
    public function __construct(
        PreparedValueFactory $preparedValueFactory,
        DeploymentConfig $deploymentConfig,
        ConfigPathResolver $configPathResolver
    ) {
        $this->preparedValueFactory = $preparedValueFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->configPathResolver = $configPathResolver;
    }

    /**
     * Processes database flow of config:set command.
     * Requires installed application.
     *
     * {@inheritdoc}
     */
    public function process($path, $value, $scope, $scopeCode)
    {
        if ($this->isLocked($path, $scope, $scopeCode)) {
            throw new CouldNotSaveException(
                __(
                    'The value you set has already been locked. To change the value, use the --%1 option.',
                    ConfigSetCommand::OPTION_LOCK
                )
            );
        }

        try {
            /** @var Value $backendModel */
            $backendModel = $this->preparedValueFactory->create($path, $value, $scope, $scopeCode);
            if ($backendModel instanceof Value) {
                $resourceModel = $backendModel->getResource();
                $resourceModel->save($backendModel);
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }
    }

    /**
     * Checks whether configuration is locked in file storage.
     *
     * @param string $path The path to configuration
     * @param string $scope The scope of configuration
     * @param string $scopeCode The scope code of configuration
     * @return bool
     */
    private function isLocked($path, $scope, $scopeCode)
    {
        $scopePath = $this->configPathResolver->resolve($path, $scope, $scopeCode, System::CONFIG_TYPE);

        return $this->deploymentConfig->get($scopePath) !== null;
    }
}
