<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;

/**
 * DB connection type resolver.
 */
class ConnectionTypeResolver implements ConnectionTypeResolverInterface
{
    /**
     * DB connection names.
     *
     * @var string[]
     */
    private $dbConnectionNames;

    /**
     * Initialize dependencies.
     *
     * @param string[] $dbConnectionNames
     */
    public function __construct(array $dbConnectionNames = [])
    {
        $this->dbConnectionNames = $dbConnectionNames;
        $this->dbConnectionNames[] = 'db';
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionType($connectionName)
    {
        return in_array($connectionName, $this->dbConnectionNames) ? 'db' : null;
    }
}
