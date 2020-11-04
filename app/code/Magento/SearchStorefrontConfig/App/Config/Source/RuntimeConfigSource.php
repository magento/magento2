<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SearchStorefrontConfig\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\App\ResourceConnection;
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
     * @var ResourceConnection
     */
    private $resourceConnection;

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
     * @param ResourceConnection $resourceConnection
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param Converter $converter
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ScopeCodeResolver $scopeCodeResolver,
        Converter $converter,
        ?DeploymentConfig $deploymentConfig = null
    ) {
        $this->resourceConnection = $resourceConnection;
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
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($connection->getTableName('core_config_data'));
            $collection = $connection->fetchAll($select);
        } catch (\DomainException $e) {
            $collection = [];
        } catch (TableNotFoundException $exception) {
            // database is empty or not setup
            $collection = [];
        }
        $config = [];
        foreach ($collection as $item) {
            if ($item['scope'] === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $config[$item['scope']][$item['path']] = $item['value'];
            } else {
                $code = $this->scopeCodeResolver->resolve($item['scope'], $item['scope_id']);
                $config[$item['scope']][$code][$item['path']] = $item['value'];
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
