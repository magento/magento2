<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Constraints\Internal;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;

/**
 * @inheritdoc
 */
class DbSchemaWriter implements DbSchemaWriterInterface
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
     * @return \Zend_Db_Statement_Interface
     */
    public function createTable($tableName, $resource, array $definition)
    {
        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            "CREATE TABLE %s (\n%s\n)",
            $adapter->quoteIdentifier($tableName),
            implode(", \n", $definition)
        );

        return $adapter->query($sql);
    }

    /**
     * Drop table from MySQL database
     *
     * @inheritdoc
     * @return \Zend_Db_Statement_Interface
     */
    public function dropTable($tableName, $resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            'DROP TABLE %s',
            $adapter->quoteIdentifier($tableName)
        );

        return $adapter->query($sql);
    }

    /**
     * For Primary key we do not need to specify name
     *
     * As MySQL do not have DROP CONSTRAINT syntax, we need different DROP statements for different operations
     *
     * @param  string $type
     * @param  string $name
     * @return string
     */
    private function getDropElementSQL($type, $name)
    {
        switch ($type) {
            case Constraint::PRIMARY_TYPE:
                return 'DROP PRIMARY KEY';
            case Constraint::UNIQUE_TYPE:
                return sprintf('DROP KEY %s', $name);
            case \Magento\Setup\Model\Declaration\Schema\Dto\Index::TYPE:
                return sprintf('DROP INDEX %s', $name);
            case Reference::TYPE:
                return sprintf('DROP FOREIGN KEY %s', $name);
            default:
                return sprintf('DROP COLUMN %s', $name);
        }
    }

    /**
     * Add element to already existed table
     * We can add three different elements: column, constraint or index
     *
     * @inheritdoc
     * @return \Zend_Db_Statement_Interface
     */
    public function addElement($elementName, $resource, $tableName, $elementDefinition, $elementType)
    {
        $addElementSyntax = $elementType === Column::TYPE ? 'ALTER TABLE %s ADD COLUMN %s' : 'ALTER TABLE %s ADD %s';

        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            $addElementSyntax,
            $adapter->quoteIdentifier($tableName),
            $elementDefinition
        );

        return $adapter->query($sql);
    }

    /**
     * Modify column and change it definition
     *
     * @inheritdoc
     * @return \Zend_Db_Statement_Interface
     */
    public function modifyColumn($resource, $tableName, $columnDefinition)
    {
        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            'ALTER TABLE %s MODIFY COLUMN %s',
            $adapter->quoteIdentifier($tableName),
            $columnDefinition
        );

        return $adapter->query($sql);
    }

    /**
     * Do Drop and Add in one query
     *
     * For example ALTER TABLE example DROP FOREIGN KEY `foreign_key`, ADD FOREIGN KEY `foreign_key` ...
     *
     * @inheritdoc
     */
    public function modifyConstraint($resource, $elementName, $tableName, $type, $constraintDefinition)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $sql = sprintf(
            'ALTER TABLE %s %s, %s',
            $adapter->quoteIdentifier($tableName),
            $this->getDropElementSQL(
                $type,
                $adapter->quoteIdentifier($elementName)
            ),
            sprintf('ADD %s', $constraintDefinition)
        );

        return $adapter->query($sql);
    }

    /**
     * @inheritdoc
     * @return \Zend_Db_Statement_Interface
     */
    public function dropElement($resource, $elementName, $tableName, $type)
    {
        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            'ALTER TABLE %s %s',
            $adapter->quoteIdentifier($tableName),
            $this->getDropElementSQL(
                $type,
                $adapter->quoteIdentifier($elementName)
            )
        );

        return $adapter->query($sql);
    }
}
