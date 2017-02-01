<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Model\Config;
use Magento\Config\Model\ConfigFactory;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Processes default flow of config:set command.
 * This processor saves the value of configuration into database.
 *
 * {@inheritdoc}
 */
class DefaultProcessor implements ConfigSetProcessorInterface
{
    /**
     * The factory that creates config model instances.
     *
     * @see Config
     * @var ConfigFactory
     */
    private $configFactory;

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
     * @param ConfigFactory $configFactory The factory that creates config model instances
     * @param DeploymentConfig $deploymentConfig The deployment configuration reader
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths according to source type
     */
    public function __construct(
        ConfigFactory $configFactory,
        DeploymentConfig $deploymentConfig,
        ConfigPathResolver $configPathResolver
    ) {
        $this->configFactory = $configFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->configPathResolver = $configPathResolver;
    }

    /**
     * Processes database flow of config:set command.
     * Requires installed application.
     *
     * {@inheritdoc}
     */
    public function process(InputInterface $input)
    {
        try {
            $path = $input->getArgument(ConfigSetCommand::ARG_PATH);
            $value = $input->getArgument(ConfigSetCommand::ARG_VALUE);
            $scope = $input->getOption(ConfigSetCommand::OPTION_SCOPE);
            $scopeCode = $input->getOption(ConfigSetCommand::OPTION_SCOPE_CODE);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }

        if (!$this->deploymentConfig->isAvailable()) {
            throw new CouldNotSaveException(
                __(
                    'We can\'t save this option because Magento is not installed. '
                    . 'To lock this value, enter the command again using the --%1 option.',
                    ConfigSetCommand::OPTION_LOCK
                )
            );
        }

        if ($this->isLocked($path, $scope, $scopeCode)) {
            throw new CouldNotSaveException(
                __(
                    'The value you set has already been locked. To change the value, use the --%1 option.',
                    ConfigSetCommand::OPTION_LOCK
                )
            );
        }

        try {
            /** @var Config $config */
            $config = $this->configFactory->create();
            $config->setDataByPath($path, $value);

            if (in_array($scope, [ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_WEBSITES])) {
                $config->setWebsite($scopeCode);
            } elseif (in_array($scope, [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES])) {
                $config->setStore($scopeCode);
            }

            $config->save();
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
