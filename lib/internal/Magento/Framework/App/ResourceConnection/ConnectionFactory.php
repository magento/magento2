<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\ResourceConnection;

use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactory as ModelConnectionFactory;

/**
 * Connection adapter factory
 */
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
