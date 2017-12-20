<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;

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
     * @param string $name
     * @param string $indexDefinition
     * @param string $elementType: can be COLUMN, CONSTRAINT or INDEX
     * @return string
     */
    private function getAddElementSQL($elementType, $name, $indexDefinition)
    {
        return sprintf('ADD %s %s %s', strtoupper($elementType), $name, $indexDefinition);
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
        $fragmentsSQL = [];

        foreach ([Constraint::TYPE, \Magento\Setup\Model\Declaration\Schema\Dto\Index::TYPE] as $elementType) {
            if (isset($definition[$elementType])) { //Some element types can be optional
                //Process indexes definition
                foreach ($definition[$elementType] as $name => $elementDefinition) {
                    $fragmentsSQL[] = $this->getAddElementSQL($elementType, $name, $elementDefinition);
                }
            }
        }

        $sql = sprintf(
            "CREATE TABLE %s (\n%s\n)",
            $tableOptions['name'],
            implode(",", $definition[Column::TYPE]),
            implode(",", $fragmentsSQL)
        );

        return $this->resourceConnection
            ->getConnection($connection)
            ->query($sql);
    }

    /**
     * Drop table from MySQL database
     *
     * @param array $tableOptions
     * @return \Zend_Db_Statement_Interface
     */
    public function dropTable(array $tableOptions)
    {
        $connection = $tableOptions['resource'];
        $tableName = $tableOptions['name'];
        $sql = sprintf(
            'DROP TABLE %s',
            $tableName
        );

        return $this->resourceConnection
            ->getConnection($connection)
            ->query($sql);
    }

    /**
     * Add element to already existed table
     * We can add three different elements: column, constraint or index
     *
     * @param array $elementOptions should consists from 3 elements: resource, elementname, tablename
     * @param string $elementDefinition
     * @param string $elementType
     * @return \Zend_Db_Statement_Interface
     */
    public function addElement(array $elementOptions, $elementDefinition, $elementType)
    {
        $connection = $elementOptions['resource'];
        $elementName = $elementOptions['element_name'];
        $tableName = $elementOptions['table_name'];

        $sql = sprintf(
            'ALTER TABLE %s %s',
            $tableName,
            $this->getAddElementSQL($elementType, $elementName, $elementDefinition)
        );

        return $this->resourceConnection
            ->getConnection($connection)
            ->query($sql);
    }

    /**
     * Modify column and change it definition
     *
     * @param array $columnOptions
     * @param string $columnDefinition
     * @return \Zend_Db_Statement_Interface
     */
    public function modifyColumn(array $columnOptions, $columnDefinition)
    {
        $connection = $columnOptions['resource'];
        $columName = $columnOptions['element_name'];
        $tableName = $columnOptions['table_name'];

        $sql = sprintf(
            'ALTER TABLE %s MODIFY COLUMN %s %s',
            $tableName,
            $columName,
            $columnDefinition
        );

        return $this->resourceConnection
            ->getConnection($connection)
            ->query($sql);
    }

    /**
     * Drop any element (constraint, column, index) from index
     *
     * @param string $elementType enum(CONSTRAINT, INDEX, COLUMN)
     * @param array $elementOptions
     * @return \Zend_Db_Statement_Interface
     */
    public function dropElement($elementType, array $elementOptions)
    {
        $connection = $elementOptions['resource'];
        $elementName = $elementOptions['element_name'];
        $tableName = $elementOptions['table_name'];

        $sql = sprintf(
            'ALTER TABLE %s DROP %s %s',
            $tableName,
            strtoupper($elementType),
            $elementName
        );

        return $this->resourceConnection
            ->getConnection($connection)
            ->query($sql);
    }
}
