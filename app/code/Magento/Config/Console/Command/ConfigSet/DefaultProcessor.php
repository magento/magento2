<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Config\Model\ConfigFactory;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\ScopePathResolver;

/**
 * Processes default flow of config:set command.
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
     * The collection factory.
     *
     * @var CollectionFactory
     */
    private $collectionFactory;

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
     * The scope path resolver.
     *
     * @var ScopePathResolver
     */
    private $scopePathResolver;

    /**
     * @param ConfigFactory $configFactory The configuration factory
     * @param CollectionFactory $collectionFactory The collection factory
     * @param DeploymentConfig $deploymentConfig The deployment config
     * @param ScopeResolverPool $scopeResolverPool The scope resolver pool
     * @param ScopePathResolver $scopePathResolver The scope path resolver
     * @param MetadataProcessor $metadataProcessor The metadata processor
     */
    public function __construct(
        ConfigFactory $configFactory,
        CollectionFactory $collectionFactory,
        DeploymentConfig $deploymentConfig,
        ScopeResolverPool $scopeResolverPool,
        ScopePathResolver $scopePathResolver,
        MetadataProcessor $metadataProcessor
    ) {
        $this->configFactory = $configFactory;
        $this->collectionFactory = $collectionFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->scopeResolverPool = $scopeResolverPool;
        $this->scopePathResolver = $scopePathResolver;
        $this->metadataProcessor = $metadataProcessor;
    }

    /**
     * Processes database flow of config:set command.
     * Requires installed application.
     *
     * {@inheritdoc}
     */
    public function process(InputInterface $input)
    {
        $path = $input->getArgument(ConfigSetCommand::ARG_PATH);
        $value = $input->getArgument(ConfigSetCommand::ARG_VALUE);
        $scope = $input->getOption(ConfigSetCommand::OPTION_SCOPE);
        $scopeCode = $input->getOption(ConfigSetCommand::OPTION_SCOPE_CODE);
        $force = $input->getOption(ConfigSetCommand::OPTION_FORCE);
        $scopeId = $this->getScopeId($scope, $scopeCode);

        if (!$this->deploymentConfig->isAvailable()) {
            throw new LocalizedException(__('Magento is not installed yet.'));
        }

        if ($this->isLocked($path, $scope, $scopeCode)) {
            throw new LocalizedException(__('Effective value already locked.'));
        }

        if ($this->getConfigItems($path, $scope, $scopeId) && !$force) {
            throw new LocalizedException((__('Config value is already exists.')));
        }

        $value = $this->metadataProcessor->prepareValue($value, $path);

        $config = $this->configFactory->create();
        $config->setDataByPath($path, $value);

        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $config->{'set' . ucfirst($scope)}($scopeCode);
        }

        $config->save();
    }

    /**
     * Retrieves configurations by search criteria.
     *
     * @param string $path The path to configuration
     * @param string $scope The scope of configuration
     * @param int $scopeId The scope identifier of configuration
     * @return array
     */
    private function getConfigItems($path, $scope, $scopeId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('path', ['like' => $path])
            ->addFieldToFilter('scope', ['like' => $scope . '%'])
            ->addFieldToFilter('scope_id', $scopeId)
            ->load();

        return $collection->getItems();
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
        $scopePath = $this->scopePathResolver->resolve($path, $scope, $scopeCode, 'system');

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
