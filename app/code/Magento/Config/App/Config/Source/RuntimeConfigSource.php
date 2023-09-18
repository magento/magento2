<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\DB\Adapter\TableNotFoundException;

/**
 * Class for retrieving runtime configuration from database.
 *
 * @api
 * @since 100.1.2
 */
class RuntimeConfigSource implements ConfigSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param Converter $converter
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ScopeCodeResolver $scopeCodeResolver,
        Converter $converter,
        ?DeploymentConfig $deploymentConfig = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->converter = $converter;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->deploymentConfig = $deploymentConfig ?? ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * Get initial data.
     *
     * @param string $path Format is scope type and scope code separated by slash: e.g. "type/code"
     * @return array
     * @since 100.1.2
     */
    public function get($path = '')
    {
        $data = new DataObject($this->deploymentConfig->isDbAvailable() ? $this->loadConfig() : []);

        return $data->getData($path) !== null ? $data->getData($path) : null;
    }

    /**
     * Load config from database.
     *
     * Load collection from db and presents it in array with path keys, like:
     * * scope/key/key *
     *
     * @return array
     */
    private function loadConfig()
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->load();
        } catch (\DomainException $e) {
            $collection = [];
        } catch (TableNotFoundException $exception) {
            // database is empty or not setup
            $collection = [];
        }
        $config = [];
        foreach ($collection as $item) {
            if ($item->getScope() === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $config[$item->getScope()][$item->getPath()] = $item->getValue();
            } else {
                $code = $this->scopeCodeResolver->resolve($item->getScope(), $item->getScopeId());
                $config[$item->getScope()][$code][$item->getPath()] = $item->getValue();
                $config[$item->getScope()][strtolower($code)][$item->getPath()] = $item->getValue();
            }
        }

        foreach ($config as $scope => &$item) {
            if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $item = $this->converter->convert($item);
            } else {
                foreach ($item as &$scopeItems) {
                    $scopeItems = $this->converter->convert($scopeItems);
                }
            }
        }
        return $config;
    }
}
