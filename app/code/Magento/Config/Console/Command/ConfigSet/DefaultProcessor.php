<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Symfony\Component\Console\Input\InputInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\StateException;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Config\Model\ResourceModel\ConfigFactory;
use Magento\Config\Console\Command\ConfigSetCommand;

/**
 * Processes default flow of config:set command.
 * This processor saves the value of configuration.
 *
 * {@inheritdoc}
 */
class DefaultProcessor implements ConfigSetProcessorInterface
{
    /**
     * The configuration factory.
     *
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * The deployment config.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The scope resolver pool.
     *
     * @var ScopeResolverPool
     */
    private $scopeResolverPool;

    /**
     * The metadata processor.
     *
     * @var MetadataProcessor
     */
    private $metadataProcessor;

    /**
     * The config path resolver.
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @param ConfigFactory $configFactory The configuration factory
     * @param DeploymentConfig $deploymentConfig The deployment config
     * @param ScopeResolverPool $scopeResolverPool The scope resolver pool
     * @param ConfigPathResolver $configPathResolver The config path resolver
     * @param MetadataProcessor $metadataProcessor The metadata processor
     */
    public function __construct(
        ConfigFactory $configFactory,
        DeploymentConfig $deploymentConfig,
        ScopeResolverPool $scopeResolverPool,
        ConfigPathResolver $configPathResolver,
        MetadataProcessor $metadataProcessor
    ) {
        $this->configFactory = $configFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->scopeResolverPool = $scopeResolverPool;
        $this->configPathResolver = $configPathResolver;
        $this->metadataProcessor = $metadataProcessor;
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
        $scopeId = $this->getScopeId($scope, $scopeCode);

        if (!$this->deploymentConfig->isAvailable()) {
            throw new StateException(
                __(
                    'Magento is not installed yet and this value can be only saved with --%1 option.',
                    ConfigSetCommand::OPTION_LOCK
                )
            );
        }

        if ($this->isLocked($path, $scope, $scopeCode)) {
            throw new CouldNotSaveException(__('Effective value already locked.'));
        }

        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scope = rtrim($scope, 's') . 's';
        }

        $value = $this->metadataProcessor->prepareValue($value, $path);

        $this->configFactory->create()->saveConfig($path, $value, $scope, $scopeId);
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
        $scopePath = $this->configPathResolver->resolve($path, $scope, $scopeCode, 'system');

        return $this->deploymentConfig->get($scopePath) !== null;
    }

    /**
     * Retrieves scope identifier by scope and scope code.
     *
     * @param string $scope The scope of configuration
     * @param string $scopeCode The scope code of configuration
     * @return int Identifier of found scope
     */
    private function getScopeId($scope, $scopeCode)
    {
        if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            return 0;
        }

        return $this->scopeResolverPool->get($scope)->getScope($scopeCode)->getId();
    }
}
