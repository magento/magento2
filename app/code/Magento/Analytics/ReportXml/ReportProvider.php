<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

use PDO;

/**
 * Providers for reports data
 */
class ReportProvider implements BatchReportProviderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var IteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var int
     */
    private $currentPosition = 0;

    /**
     * @var int
     */
    private $countTotal = 0;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var Query
     */
    private $dataSelect;

    /**
     * @var int|null Last cursor value for cursor-based pagination
     */
    private ?int $lastCursor = null;

    /**
     * @var string|null Cursor column name for cursor-based pagination
     */
    private ?string $cursorColumn = null;

    /**
     * ReportProvider constructor.
     *
     * @param QueryFactory $queryFactory
     * @param ConnectionFactory $connectionFactory
     * @param IteratorFactory $iteratorFactory
     */
    public function __construct(
        QueryFactory $queryFactory,
        ConnectionFactory $connectionFactory,
        IteratorFactory $iteratorFactory
    ) {
        $this->queryFactory = $queryFactory;
        $this->connectionFactory = $connectionFactory;
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * Returns custom iterator name for report. Null for default
     *
     * @param Query $query
     * @return string|null
     */
    private function getIteratorName(Query $query)
    {
        $config = $query->getConfig();
        return $config['iterator'] ?? null;
    }

    /**
     * Returns report data by name and criteria
     *
     * @param string $name
     * @return \IteratorIterator
     */
    public function getReport($name)
    {
        $query = $this->queryFactory->create($name);
        $connection = $this->connectionFactory->getConnection($query->getConnectionName());
        $statement = $connection->query($query->getSelect());
        return $this->iteratorFactory->create($statement, $this->getIteratorName($query));
    }

    /**
     * @inheritdoc
     */
    public function getBatchReport(string $name): \IteratorIterator
    {
        if (!$this->dataSelect || $this->dataSelect->getConfig()['name'] !== $name) {
            $this->dataSelect = $this->queryFactory->create($name);
            $this->lastCursor = null;
            $this->currentPosition = 0;
            $this->countTotal = 0;
            $this->connection = $this->connectionFactory->getConnection($this->dataSelect->getConnectionName());
            $this->cursorColumn = $this->getCursorColumn();
            if (!$this->cursorColumn) {
                $this->countTotal = $this->connection->fetchOne($this->dataSelect->getSelectCountSql());
            }
        }
        if (!$this->cursorColumn) {
            return $this->getBatchReportWithOffset();
        }

        $select = clone $this->dataSelect->getSelect();
        $cursorValue = $this->lastCursor ?? 0;
        $select->where(sprintf('%s > ?', $this->cursorColumn), $cursorValue);
        $select->order(sprintf('%s ASC', $this->cursorColumn));
        $select->limit(self::BATCH_SIZE);
        $statement = $this->connection->query($select);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return $this->iteratorFactory->create(new \ArrayIterator([]), $this->getIteratorName($this->dataSelect));
        }
        $lastRow = $rows[count($rows) - 1];
        $this->lastCursor = $lastRow[$this->cursorColumn] ?? null;
        return $this->iteratorFactory->create(new \ArrayIterator($rows), $this->getIteratorName($this->dataSelect));
    }

    /**
     * Detect cursor column based on the source table's primary key
     *
     * @return string|null Returns the primary key column name, or null if not found
     */
    private function getCursorColumn(): ?string
    {
        $config = $this->dataSelect->getConfig();
        $tableName = $config['source']['name'] ?? null;
        $analyticTables = ["customer_entity", "sales_order", "sales_order_address", "quote", "catalog_product_entity"];
        if ($tableName) {
            if (in_array($tableName, $analyticTables)) {
                return "entity_id";
            } elseif ($tableName == "sales_order_item") {
                return "item_id";
            }
        }
        return null;
    }

    /**
     * Fallback to offset-based pagination when cursor column cannot be detected
     *
     * @return \IteratorIterator
     */
    private function getBatchReportWithOffset(): \IteratorIterator
    {
        if ($this->currentPosition >= $this->countTotal) {
            return $this->iteratorFactory->create(new \ArrayIterator([]), $this->getIteratorName($this->dataSelect));
        }
        $statement = $this->connection->query(
            $this->dataSelect->getSelect()->limit(self::BATCH_SIZE, $this->currentPosition)
        );
        $this->currentPosition += self::BATCH_SIZE;
        return $this->iteratorFactory->create($statement, $this->getIteratorName($this->dataSelect));
    }
}
