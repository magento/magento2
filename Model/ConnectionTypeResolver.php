<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;

/**
 * DB connection type resolver.
 * @since 2.2.0
 */
class ConnectionTypeResolver implements ConnectionTypeResolverInterface
{
    /**
     * DB connection names.
     *
     * @var string[]
     * @since 2.2.0
     */
    private $dbConnectionNames;

    /**
     * Initialize dependencies.
     *
     * @param string[] $dbConnectionNames
     * @since 2.2.0
     */
    public function __construct(array $dbConnectionNames = [])
    {
        $this->dbConnectionNames = $dbConnectionNames;
        $this->dbConnectionNames[] = 'db';
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getConnectionType($connectionName)
    {
        return in_array($connectionName, $this->dbConnectionNames) ? 'db' : null;
    }
}
