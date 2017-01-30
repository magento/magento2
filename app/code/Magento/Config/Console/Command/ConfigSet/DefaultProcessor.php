<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Symfony\Component\Console\Input\InputInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Model\ConfigFactory;
use Magento\Config\App\Config\Type\System;
use Magento\Store\Model\ScopeInterface;

/**
 * Processes default flow of config:set command.
 * This processor saves the value of configuration.
 *
 * {@inheritdoc}
 */
class DefaultProcessor implements ConfigSetProcessorInterface
{
    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @param ConfigFactory $configFactory
     * @param DeploymentConfig $deploymentConfig
     * @param ConfigPathResolver $configPathResolver
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
     * @throws StateException
     */
    public function process(InputInterface $input)
    {
        $path = $input->getArgument(ConfigSetCommand::ARG_PATH);
        $value = $input->getArgument(ConfigSetCommand::ARG_VALUE);
        $scope = $input->getOption(ConfigSetCommand::OPTION_SCOPE);
        $scopeCode = $input->getOption(ConfigSetCommand::OPTION_SCOPE_CODE);

        if (!$this->deploymentConfig->isAvailable()) {
            throw new StateException(
                __(
                    'Magento is not installed yet and this value can be only saved with --%1 option.',
                    ConfigSetCommand::OPTION_LOCK
                )
            );
        }

        if ($this->isLocked($path, $scope, $scopeCode)) {
            throw new CouldNotSaveException(
                __(
                    'Effective value already locked. It can be changed with --%1 option',
                    ConfigSetCommand::OPTION_LOCK
                )
            );
        }

        $config = $this->configFactory->create();
        $config->setDataByPath($path, $value);

        if (in_array($scope, [ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_WEBSITES])) {
            $config->setWebsite($scopeCode);
        } elseif (in_array($scope, [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES])) {
            $config->setStore($scopeCode);
        }

        $config->save();
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
