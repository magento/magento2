<?php
/**
 * Connection adapter factory
 *
 * Copyright © 2015 Magento. All rights reserved.
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
        /** @var \Magento\Framework\App\Cache\Type\FrontendPool $pool */
        $pool = $this->objectManager->get(\Magento\Framework\App\Cache\Type\FrontendPool::class);
        $connection->setCacheAdapter($pool->get(DdlCache::TYPE_IDENTIFIER));
        return $connection;
    }
}
