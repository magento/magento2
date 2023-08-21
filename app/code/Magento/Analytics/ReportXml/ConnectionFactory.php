<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection\ConfigInterface as ResourceConfigInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;

/**
 * Creates connection instance for export according to existing one
 *
 * This connection does not use buffered statement, also this connection is not persistent
 */
class ConnectionFactory
{
    /**
     * @var ResourceConfigInterface
     */
    private $resourceConfig;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ConnectionFactoryInterface
     */
    private $connectionFactory;

    /**
     * @param ResourceConfigInterface $resourceConfig
     * @param DeploymentConfig $deploymentConfig
     * @param ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(
        ResourceConfigInterface $resourceConfig,
        DeploymentConfig $deploymentConfig,
        ConnectionFactoryInterface $connectionFactory
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * Creates one-time connection for export
     *
     * @param string $resourceName
     * @return AdapterInterface
     */
    public function getConnection($resourceName)
    {
        $connectionName = $this->resourceConfig->getConnectionName($resourceName);
        $configData = $this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/' . $connectionName
        );
        $configData['use_buffered_query'] = false;
        unset($configData['persistent']);
        $connection = $this->connectionFactory->create($configData);

        return $connection;
    }
}
