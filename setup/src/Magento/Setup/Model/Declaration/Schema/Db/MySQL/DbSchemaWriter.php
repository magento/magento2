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
     * Prepare constraint statement by compiling name and definition together
     *
     * @param  string $name
     * @param  string $indexDefinition
     * @param  string $elementType:    can be COLUMN, CONSTRAINT or INDEX
     * @return string
     */
    private function getAddElementSQL($elementType, $name, $indexDefinition)
    {
        return sprintf('ADD %s %s %s', strtoupper($elementType), $name, $indexDefinition);
    }

    /**
     * Convert definition from format:
     *  $name => $definition
     * To format:
     *  $name $definition
     *
     * @param  array            $columnsDefinition
     * @param  AdapterInterface $adapter
     * @return array
     */
    private function getColumnsWithNames(array $columnsDefinition, AdapterInterface $adapter)
    {
        $definition = [];
        foreach ($columnsDefinition as $name => $columnDefinition) {
            $definition[] = sprintf(
                "%s %s",
                $adapter->quoteIdentifier($name),
                $columnDefinition
            );
        }

        return $definition;
    }

    /**
     * @inheritdoc
     * @return \Zend_Db_Statement_Interface
     */
    public function createTable($tableName, $resource, array $definition)
    {
        $fragmentsSQL = [];
        $adapter = $this->resourceConnection->getConnection($resource);

        foreach ([Constraint::TYPE, \Magento\Setup\Model\Declaration\Schema\Dto\Index::TYPE] as $elementType) {
            if (isset($definition[$elementType])) { //Some element types can be optional
                //Process indexes definition
                foreach ($definition[$elementType] as $name => $elementDefinition) {
                    $fragmentsSQL[] = sprintf(
                        '%s %s %s',
                        strtoupper($elementType),
                        $adapter->quoteIdentifier($name),
                        $elementDefinition
                    );
                }
            }
        }

        $sql = sprintf(
            "CREATE TABLE %s (\n%s,\n%s\n)",
            $adapter->quoteIdentifier($tableName),
            implode(", \n", $this->getColumnsWithNames($definition[Column::TYPE], $adapter)),
            implode(", \n", $fragmentsSQL)
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
        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            'ALTER TABLE %s %s',
            $adapter->quoteIdentifier($tableName),
            $this->getAddElementSQL(
                $elementType,
                $adapter->quoteIdentifier($elementName),
                $elementDefinition
            )
        );

        return $adapter->query($sql);
    }

    /**
     * Modify column and change it definition
     *
     * @inheritdoc
     * @return \Zend_Db_Statement_Interface
     */
    public function modifyColumn($resource, $columnName, $tableName, $columnDefinition)
    {
        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            'ALTER TABLE %s MODIFY COLUMN %s %s',
            $adapter->quoteIdentifier($tableName),
            $adapter->quoteIdentifier($columnName),
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
            $this->getAddElementSQL(
                Constraint::TYPE,
                //We need to avoid `PRIMARY` name in modify constraint syntax
                $elementName === Internal::PRIMARY_NAME ? '' : $adapter->quoteIdentifier($elementName),
                $constraintDefinition
            )
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
