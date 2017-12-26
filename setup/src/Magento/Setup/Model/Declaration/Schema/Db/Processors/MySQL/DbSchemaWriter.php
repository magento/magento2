<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Constraints\ForeignKey;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Constraints\Internal;
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
     * @param array $tableOptions
     * @param array $definition
     * @return \Zend_Db_Statement_Interface
     */
    public function createTable(array $tableOptions, array $definition)
    {
        $connection = $tableOptions['resource'];
        $fragmentsSQL = [];
        $adapter = $this->resourceConnection->getConnection($connection);

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
            $adapter->quoteIdentifier($tableOptions['name']),
            implode(", \n", $this->getColumnsWithNames($definition[Column::TYPE], $adapter)),
            implode(", \n", $fragmentsSQL)
        );

        return $adapter->query($sql);
    }

    /**
     * Drop table from MySQL database
     *
     * @param  array $tableOptions
     * @return \Zend_Db_Statement_Interface
     */
    public function dropTable(array $tableOptions)
    {
        $connection = $tableOptions['resource'];
        $tableName = $tableOptions['name'];
        $adapter = $this->resourceConnection->getConnection($connection);

        $sql = sprintf(
            'DROP TABLE %s',
            $adapter->quoteIdentifier($tableName)
        );

        return $adapter->query($sql);
    }

    /**
     * For Primary key we do not need to specify name
     *
     * @param  string $elementType
     * @param  string $type
     * @param  string $name
     * @return string
     */
    private function getDropElementSQL($elementType, $type, $name)
    {
        if ($elementType === Constraint::TYPE) {
            if ($type === 'primary') {
                return 'DROP PRIMARY KEY';
            }

            $elementType = $this->getConstraintType($type);
        }

        return sprintf(
            'DROP %s %s',
            strtoupper($elementType),
            $name
        );
    }

    /**
     * Add element to already existed table
     * We can add three different elements: column, constraint or index
     *
     * @param  array  $elementOptions    should consists from 3 elements: resource, elementname, tablename
     * @param  string $elementDefinition
     * @param  string $elementType
     * @return \Zend_Db_Statement_Interface
     */
    public function addElement(array $elementOptions, $elementDefinition, $elementType)
    {
        $connection = $elementOptions['resource'];
        $elementName = $elementOptions['element_name'];
        $tableName = $elementOptions['table_name'];
        $adapter = $this->resourceConnection->getConnection($connection);

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
     * @param  array  $columnOptions
     * @param  string $columnDefinition
     * @return \Zend_Db_Statement_Interface
     */
    public function modifyColumn(array $columnOptions, $columnDefinition)
    {
        $connection = $columnOptions['resource'];
        $columName = $columnOptions['element_name'];
        $tableName = $columnOptions['table_name'];
        $adapter = $this->resourceConnection->getConnection($connection);

        $sql = sprintf(
            'ALTER TABLE %s MODIFY COLUMN %s %s',
            $adapter->quoteIdentifier($tableName),
            $adapter->quoteIdentifier($columName),
            $columnDefinition
        );

        return $adapter->query($sql);
    }

    /**
     * Detect what type of constraint we have
     *
     * @param  string $type
     * @return string
     */
    private function getConstraintType($type)
    {
        switch ($type) {
        case 'foreign':
            $elementType = ForeignKey::FOREIGN_KEY_NAME;
            break;
        case 'primary':
            $elementType = Internal::PRIMARY_KEY_NAME;
            break;
        case 'unique':
        case 'index':
            //In MySQL for unique and for index drop syntax is the same
            $elementType = Index::INDEX_KEY_NAME;
            break;
        default:
            $elementType = Constraint::TYPE;
        }

        return $elementType;
    }

    /**
     * Do Drop and Add in one query
     *
     * For example ALTER TABLE example DROP FOREIGN KEY `foreign_key`, ADD FOREIGN KEY `foreign_key` ...
     *
     * @inheritdoc
     */
    public function modifyConstraint(array $constraintOptions, $constraintDefinition)
    {
        $connection = $constraintOptions['resource'];
        $elementName = $constraintOptions['element_name'];
        $tableName = $constraintOptions['table_name'];
        $type = $constraintOptions['type'];
        $adapter = $this->resourceConnection->getConnection($connection);

        $sql = sprintf(
            'ALTER TABLE %s %s, %s',
            $adapter->quoteIdentifier($tableName),
            $this->getDropElementSQL(
                Constraint::TYPE,
                $type,
                $adapter->quoteIdentifier($elementName)
            ),
            $this->getAddElementSQL(
                Constraint::TYPE,
                //We need to ignore name for PRIMARY KEY
                $elementName === Internal::PRIMARY_NAME ?
                    '' : $adapter->quoteIdentifier($elementName),
                $constraintDefinition
            )
        );

        return $adapter->query($sql);
    }

    /**
     * Drop any element (constraint, column, index) from index
     *
     * @param  string $elementType    enum(CONSTRAINT, INDEX, COLUMN)
     * @param  array  $elementOptions
     * @return \Zend_Db_Statement_Interface
     */
    public function dropElement($elementType, array $elementOptions)
    {
        $connection = $elementOptions['resource'];
        $elementName = $elementOptions['element_name'];
        $tableName = $elementOptions['table_name'];
        $type = $elementOptions['type'];
        $adapter = $this->resourceConnection->getConnection($connection);

        $sql = sprintf(
            'ALTER TABLE %s %s',
            $adapter->quoteIdentifier($tableName),
            $this->getDropElementSQL(
                $elementType,
                $type,
                $adapter->quoteIdentifier($elementName)
            )
        );

        return $adapter->query($sql);
    }
}
