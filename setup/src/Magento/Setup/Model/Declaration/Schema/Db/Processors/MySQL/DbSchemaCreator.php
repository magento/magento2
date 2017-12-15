<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaCreatorInterface;
use Magento\Setup\Model\Declaration\Schema\Sharding;

/**
 * @inheritdoc
 */
class DbSchemaCreator implements DbSchemaCreatorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     * @param array $tableOptions
     * @param array $sqlFragments
     * @return \Zend_Db_Statement_Interface
     */
    public function createTable(array $tableOptions, array $sqlFragments)
    {
        $connection = isset($tableOptions['connection']) ?
            $tableOptions['connection'] : Sharding::DEFAULT_CONNECTION;
        $sql = sprintf(
            "CREATE TABLE IF NOT EXISTS %s (\n%s\n)",
            $tableOptions['name'],
            $sqlFragments[self::COLUMN_FRAGMENT]
        );

        return $this->resourceConnection
            ->getConnection($connection)
            ->query($sql);
    }
}
