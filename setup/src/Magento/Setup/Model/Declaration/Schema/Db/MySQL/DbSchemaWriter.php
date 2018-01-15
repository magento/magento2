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
use Magento\Setup\Model\Declaration\Schema\Db\Statement;
use Magento\Setup\Model\Declaration\Schema\Db\StatementAggregator;
use Magento\Setup\Model\Declaration\Schema\Db\StatementFactory;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;

/**
 * @inheritdoc
 */
class DbSchemaWriter implements DbSchemaWriterInterface
{
    /**
     * Statement directives with which we will decide what to do with tables
     *
     * @var array
     */
    private $statementDirectives = [
        self::ALTER_TYPE => 'ALTER TABLE %s %s',
        self::CREATE_TYPE => 'CREATE TABLE %s %s',
        self::DROP_TYPE => 'DROP TABLE %s'
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StatementFactory
     */
    private $statementFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StatementFactory $statementFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StatementFactory $statementFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->statementFactory = $statementFactory;
    }

    /**
     * @inheritdoc
     */
    public function createTable($tableName, $resource, array $definition, array $options)
    {
        $sql = sprintf(
            "(\n%s\n) ENGINE=%s",
            implode(", \n", $definition),
            $options['engine']
        );

        return $this->statementFactory->create(
            $tableName,
            $tableName,
            self::CREATE_TYPE,
            $sql,
            $resource
        );
    }

    /**
     * Drop table from MySQL database
     *
     * @inheritdoc
     */
    public function dropTable($tableName, $resource)
    {
        return $this->statementFactory->create(
            $tableName,
            $tableName,
            self::DROP_TYPE,
            '',
            $resource
        );
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
     */
    public function addElement($elementName, $resource, $tableName, $elementDefinition, $elementType)
    {
        $addElementSyntax = $elementType === Column::TYPE ? 'ADD COLUMN %s' : 'ADD %s';
        $sql = sprintf(
            $addElementSyntax,
            $elementDefinition
        );
        return $this->statementFactory->create(
            $elementName,
            $tableName,
            self::ALTER_TYPE,
            $sql,
            $resource,
            $elementType
        );
    }

    /**
     * Modify column and change it definition
     *
     * @inheritdoc
     */
    public function modifyColumn($columnName, $resource, $tableName, $columnDefinition)
    {
        $sql = sprintf(
            'MODIFY COLUMN %s',
            $columnDefinition
        );
        return $this->statementFactory->create(
            $columnName,
            $tableName,
            self::ALTER_TYPE,
            $sql,
            $resource
        );
    }

    /**
     * @inheritdoc
     */
    public function dropElement($resource, $elementName, $tableName, $type)
    {
        $adapter = $this->resourceConnection->getConnection($resource);

        $sql = sprintf(
            '%s',
            $this->getDropElementSQL(
                $type,
                $adapter->quoteIdentifier($elementName)
            )
        );
        return $this->statementFactory->create(
            $elementName,
            $tableName,
            self::ALTER_TYPE,
            $sql,
            $resource,
            $type
        );
    }

    /**
     * @inheritdoc
     */
    public function compile(StatementAggregator $statementAggregator)
    {
        foreach ($statementAggregator->getStatementsBank() as $statementBank) {
            $statementsSql = [];
            /** @var Statement $statement */
            foreach ($statementBank as $statement) {
                $statementsSql[] = $statement->getStatement();
            }

            $adapter = $this->resourceConnection->getConnection($statement->getResource());
            $adapter->query(
                sprintf(
                    $this->statementDirectives[$statement->getType()],
                    $adapter->quoteIdentifier($statement->getTableName()),
                    implode(", ", $statementsSql)
                )
            );
            //Do post update, like SQL DML operations or etc...
            foreach ($statement->getTriggers() as $trigger) {
                call_user_func($trigger);
            }
        }
    }
}
