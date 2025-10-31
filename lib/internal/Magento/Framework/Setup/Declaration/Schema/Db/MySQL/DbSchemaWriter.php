<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\Statement;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementAggregator;
use Magento\Framework\Setup\Declaration\Schema\Db\StatementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\Framework\Setup\Declaration\Schema\Dto\Factories\Table as DtoFactoriesTable;

/**
 * @inheritdoc
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DbSchemaWriter implements DbSchemaWriterInterface
{
    /**
     * Statement directives with which we will decide what to do with tables.
     *
     * @var array
     */
    private $statementDirectives = [
        self::ALTER_TYPE => 'ALTER TABLE %s %s',
        self::CREATE_TYPE => 'CREATE TABLE %s %s',
        self::DROP_TYPE => 'DROP TABLE %s'
    ];

    /**
     * Table options mapping
     *
     * @var array
     */
    private $tableOptions = [
        'charset' => 'DEFAULT CHARSET',
        'collation' => 'DEFAULT COLLATE',
        'engine' => 'ENGINE',
        'comment' => 'COMMENT'
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
     * @var DryRunLogger
     */
    private $dryRunLogger;

    /**
     * @var SqlVersionProvider
     */
    private $sqlVersionProvider;

    /***
     * @var DtoFactoriesTable
     */
    private DtoFactoriesTable $columnConfig;

    private const TEXTUAL_COLUMN_TYPES = ['varchar', 'char', 'text', 'mediumtext', 'longtext'];

    /**
     * @param ResourceConnection $resourceConnection
     * @param StatementFactory $statementFactory
     * @param DryRunLogger $dryRunLogger
     * @param SqlVersionProvider $sqlVersionProvider
     * @param DtoFactoriesTable|null $dtoFactoriesTable
     * @param array $tableOptions
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StatementFactory $statementFactory,
        DryRunLogger $dryRunLogger,
        SqlVersionProvider $sqlVersionProvider,
        ?DtoFactoriesTable $dtoFactoriesTable = null,
        array $tableOptions = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->statementFactory = $statementFactory;
        $this->dryRunLogger = $dryRunLogger;
        $this->columnConfig = $dtoFactoriesTable ?: ObjectManager::getInstance()->get(DtoFactoriesTable::class);
        $this->tableOptions = array_replace($this->tableOptions, $tableOptions);
        $this->sqlVersionProvider = $sqlVersionProvider;
    }

    /**
     * @inheritdoc
     */
    public function createTable($tableName, $resource, array $definition, array $options)
    {
        if (count($definition)) {
            foreach ($definition as $index => $value) {
                if ($this->isColumnTypes($value, self::TEXTUAL_COLUMN_TYPES)) {
                    if (str_contains($index, 'column')) {
                        $definition[$index] = $this->setDefaultCharsetAndCollation($value);
                    }
                }
            }
        }
        $sql = sprintf(
            "(\n%s\n) ENGINE=%s DEFAULT CHARSET=%s DEFAULT COLLATE=%s %s",
            implode(", \n", $definition),
            $options['engine'],
            $options['charset'],
            $options['collation'],
            isset($options['comment']) ? sprintf('COMMENT="%s"', $options['comment']) : ''
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
     * No names specified for Primary Keys.
     *
     * As MySQL do not have DROP CONSTRAINT syntax, different DROP statements for different operations are required.
     *
     * @param  string $type
     * @param  string $name
     * @return string
     */
    private function getDropElementSQL($type, $name)
    {
        $result = sprintf('DROP COLUMN %s', $name);
        switch ($type) {
            case Constraint::PRIMARY_TYPE:
                $result = 'DROP PRIMARY KEY';
                break;
            case Constraint::UNIQUE_TYPE:
                $result = sprintf('DROP KEY %s', $name);
                break;
            case \Magento\Framework\Setup\Declaration\Schema\Dto\Index::TYPE:
                $result = sprintf('DROP INDEX %s', $name);
                break;
            case Reference::TYPE:
                $result = sprintf('DROP FOREIGN KEY %s', $name);
                break;
        }

        return $result;
    }

    /**
     * @inheritdoc
     *
     * @param string $elementName
     * @param string $resource
     * @param string $tableName
     * @param string $elementDefinition , for example: like CHAR(200) NOT NULL
     * @param string $elementType
     * @return Statement
     */
    public function addElement($elementName, $resource, $tableName, $elementDefinition, $elementType)
    {
        $addElementSyntax = $elementType === Column::TYPE ? 'ADD COLUMN %s' : 'ADD %s';
        if ($elementType === Column::TYPE) {
            if ($this->isColumnTypes($elementDefinition, self::TEXTUAL_COLUMN_TYPES)) {
                $elementDefinition = $this->setDefaultCharsetAndCollation($elementDefinition);
            }
        }
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
     * @inheritdoc
     *
     * @param string $tableName
     * @param string $resource
     * @param string $optionName
     * @param string $optionValue
     * @return Statement
     */
    public function modifyTableOption($tableName, $resource, $optionName, $optionValue)
    {
        return $this->statementFactory->create(
            $tableName,
            $tableName,
            self::ALTER_TYPE,
            sprintf("%s='%s'", $this->tableOptions[$optionName], $optionValue),
            $resource
        );
    }

    /**
     * @inheritdoc
     *
     * @param  string $columnName
     * @param  string $resource
     * @param  string $tableName
     * @param  string $columnDefinition
     * @return Statement
     */
    public function modifyColumn($columnName, $resource, $tableName, $columnDefinition)
    {
        if ($this->isColumnTypes($columnDefinition, self::TEXTUAL_COLUMN_TYPES)) {
            $columnDefinition = $this->setDefaultCharsetAndCollation($columnDefinition);
        }

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
     *
     * @param string $resource
     * @param string $elementName
     * @param string $tableName
     * @param string $type
     * @return Statement
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
    public function resetAutoIncrement($tableName, $resource)
    {
        $autoIncrementValue = $this->getNextAutoIncrementValue($tableName, $resource);
        $sql = "AUTO_INCREMENT = {$autoIncrementValue}";

        return $this->statementFactory->create(
            sprintf('RESET_AUTOINCREMENT_%s', $tableName),
            $tableName,
            self::ALTER_TYPE,
            $sql,
            $resource
        );
    }

    /**
     * @inheritdoc
     */
    public function compile(StatementAggregator $statementAggregator, $dryRun)
    {
        foreach ($statementAggregator->getStatementsBank() as $statementBank) {
            $statementsSql = [];
            $statement = null;

            if ($dryRun) {
                /**
                 * @var Statement $statement
                 */
                foreach ($statementBank as $statement) {
                    $statementsSql[] = $statement->getStatement();
                }
                $adapter = $this->resourceConnection->getConnection($statement->getResource());
                $this->dryRunLogger->log(
                    sprintf(
                        $this->statementDirectives[$statement->getType()],
                        $adapter->quoteIdentifier($statement->getTableName()),
                        implode(", ", $statementsSql)
                    )
                );
            } else {
                $this->doQuery($statementBank);
                $statement = end($statementBank);
                //Do post update, like SQL DML operations or etc...
                foreach ($statement->getTriggers() as $trigger) {
                    call_user_func($trigger);
                }
            }
        }
    }

    /**
     * Check if we can concatenate sql into one statement
     *
     * Due to issues with some versions of MariaBD such statements
     * may produce errors, e.g. with foreign key definition with column modification
     *
     * @return bool
     * @throws ConnectionException
     */
    private function isNeedToSplitSql() : bool
    {
        return str_contains($this->sqlVersionProvider->getSqlVersion(), SqlVersionProvider::MARIA_DB_10_4_VERSION) ||
            str_contains($this->sqlVersionProvider->getSqlVersion(), SqlVersionProvider::MARIA_DB_10_6_VERSION) ||
            str_contains($this->sqlVersionProvider->getSqlVersion(), SqlVersionProvider::MARIA_DB_11_4_VERSION);
    }

    /**
     * Perform queries based on statements
     *
     * @param Statement[] $statementBank
     * @return void
     * @throws ConnectionException
     */
    private function doQuery(
        array $statementBank
    ) : void {
        if (empty($statementBank)) {
            return;
        }

        $statement = null;
        $statementsSql = [];
        foreach ($statementBank as $statement) {
            $statementsSql[] = $statement->getStatement();
        }
        $adapter = $this->resourceConnection->getConnection($statement->getResource());

        if ($this->isNeedToSplitSql()) {
            $preparedStatements = $this->getPreparedStatements($statementBank);

            if (!empty($preparedStatements['canBeCombinedStatements'])) {
                $adapter->query(
                    sprintf(
                        $this->statementDirectives[$statement->getType()],
                        $adapter->quoteIdentifier($statement->getTableName()),
                        implode(", ", $preparedStatements['canBeCombinedStatements'])
                    )
                );
            }
            foreach ($preparedStatements['separatedStatements'] as $separatedStatement) {
                $adapter->query(
                    sprintf(
                        $this->statementDirectives[$statement->getType()],
                        $adapter->quoteIdentifier($statement->getTableName()),
                        $separatedStatement
                    )
                );
            }
        } else {
            $adapter->query(
                sprintf(
                    $this->statementDirectives[$statement->getType()],
                    $adapter->quoteIdentifier($statement->getTableName()),
                    implode(", ", $statementsSql)
                )
            );
        }
    }

    /**
     * Retrieve next value for AUTO_INCREMENT column.
     *
     * @param string $tableName
     * @param string $resource
     * @return int
     * @throws \Zend_Db_Statement_Exception
     */
    private function getNextAutoIncrementValue(string $tableName, string $resource): int
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $autoIncrementField = $adapter->getAutoIncrementField($tableName);
        if ($autoIncrementField) {
            $sql = sprintf('SELECT MAX(`%s`) + 1 FROM `%s`', $autoIncrementField, $tableName);
            $adapter->resetDdlCache($tableName);
            $stmt = $adapter->query($sql);

            return (int)$stmt->fetchColumn();
        } else {
            return 1;
        }
    }

    /**
     * Prepare list of modified columns from statement
     *
     * @param array $statementBank
     * @return array
     */
    private function getModifiedColumns(array $statementBank) : array
    {
        $columns = [];
        foreach ($statementBank as $statement) {
            if ($statement->getType() === 'alter'
                && str_contains($statement->getStatement(), 'MODIFY COLUMN')) {
                $columns[] = $statement->getName();
            }
        }
        return $columns;
    }

    /**
     * Separate statements that can't be executed as one statement
     *
     * @param array $statementBank
     * @return array
     */
    private function getPreparedStatements(array $statementBank) : array
    {
        $statementsSql = [];
        foreach ($statementBank as $statement) {
            $statementsSql[] = $statement->getStatement();
        }
        $result = ['separatedStatements' => [], 'canBeCombinedStatements' => []];
        $modifiedColumns = $this->getModifiedColumns($statementBank);

        foreach ($statementsSql as $statementSql) {
            if (str_contains($statementSql, 'FOREIGN KEY')) {
                $isThisColumnModified = false;
                foreach ($modifiedColumns as $modifiedColumn) {
                    if (str_contains($statementSql, '`' . $modifiedColumn . '`')) {
                        $isThisColumnModified = true;
                        break;
                    }
                }
                if ($isThisColumnModified) {
                    $result['separatedStatements'][] = $statementSql;
                } else {
                    $result['canBeCombinedStatements'][] = $statementSql;
                }
            } else {
                $result['canBeCombinedStatements'][] = $statementSql;
            }
        }
        return $result;
    }

    /**
     * Set default collation & charset (e.g. utf8mb4_general_ci and utf8mb4) for tables
     *
     * @param string $columnDefinition
     * @return string
     */
    private function setDefaultCharsetAndCollation(string $columnDefinition): string
    {
        $charset = $this->columnConfig->getDefaultCharset();
        $collate = $this->columnConfig->getDefaultCollation();
        $columnLevelConfig = 'CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
        $columnsAttribute  = explode(' ', $columnDefinition);
        array_splice($columnsAttribute, 2, 0, $columnLevelConfig);
        return implode(" ", $columnsAttribute);
    }

    /**
     * Checks if any column is of the types passed in $columnTypes
     *
     * @param string $definition
     * @param array $columnTypes
     * @return bool
     */
    private function isColumnTypes(string $definition, array $columnTypes): bool
    {
        $type = explode(' ', $definition);
        $pattern = '/\b(' . implode('|', array_map('preg_quote', $columnTypes)) . ')\b/i';
        return preg_match($pattern, $type[1]) === 1;
    }
}
