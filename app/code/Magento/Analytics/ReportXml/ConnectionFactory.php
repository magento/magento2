<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class ConnectionFactory
 *
 * Creates connection instance for export according to existing one
 * This connection does not use buffered statement, also this connection is not persistent
 * @since 2.2.0
 */
class ConnectionFactory
{
    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * ConnectionFactory constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param ObjectManagerInterface $objectManager
     * @since 2.2.0
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->objectManager = $objectManager;
    }

    /**
     * Creates one-time connection for export
     *
     * @param string $connectionName
     * @return AdapterInterface
     * @since 2.2.0
     */
    public function getConnection($connectionName)
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $connectionClassName = get_class($connection);
        $configData = $connection->getConfig();
        $configData['use_buffered_query'] = false;
        unset($configData['persistent']);
        return $this->objectManager->create(
            $connectionClassName,
            [
                'config' => $configData
            ]
        );
    }
}
