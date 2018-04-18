<?php
/**
 * Connection adapter factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactory as ModelConnectionFactory;
use Magento\Framework\DB\Adapter\DdlCache;

class ConnectionFactory extends ModelConnectionFactory
{
    /**
     * Create connection adapter instance
     *
     * @param array $connectionConfig
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function create(array $connectionConfig)
    {
        $connection = parent::create($connectionConfig);
        /** @var \Magento\Framework\DB\Adapter\DdlCache $ddlCache */
        $ddlCache = $this->objectManager->get(\Magento\Framework\DB\Adapter\DdlCache::class);
        $connection->setCacheAdapter($ddlCache);
        return $connection;
    }
}
