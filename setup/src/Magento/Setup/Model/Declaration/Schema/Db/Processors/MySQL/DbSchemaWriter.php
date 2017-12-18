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
class DbSchemaWriter implements DbSchemaCreatorInterface
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
     * Prepare constraint statement by compiling name and definition together
     *
     * @param string $name
     * @param string $constraintDefinition
     * @return string
     */
    private function getAddConstraintSQL($name, $constraintDefinition)
    {
        return sprintf('ADD CONSTRAINT %s %s', $name, $constraintDefinition);
    }

    /**
     * Prepare constraint statement by compiling name and definition together
     *
     * @param string $name
     * @param string $indexDefinition
     * @return string
     */
    private function getAddIndexSQL($name, $indexDefinition)
    {
        return sprintf('ADD INDEX %s %s', $name, $indexDefinition);
    }

    /**
     * @inheritdoc
     * @param array $tableOptions
     * @param array $definition
     * @return \Zend_Db_Statement_Interface
     */
    public function createTable(array $tableOptions, array $definition)
    {
        $connection = $tableOptions['resource'];
        $indecisSQL = [];
        $constraintsSQL = [];

        foreach ($definition[self::INDEX_FRAGMENT] as $name => $indexDefinition) {
            $indecisSQL[] = $this->getIndexSQL($name, $indexDefinition);
        }

        foreach ($definition[self::CONSTRAINT_FRAGMENT] as $name => $constraintDefinition) {
            $constraintsSQL[] = $this->getAddConstraintSQL($name, $constraintDefinition);
        }

        $sql = sprintf(
            "CREATE TABLE IF NOT EXISTS %s (\n%s\n)",
            $tableOptions['name'],
            $definition[self::COLUMN_FRAGMENT]
        );

        return $this->resourceConnection
            ->getConnection($connection)
            ->query($sql);
    }
}
