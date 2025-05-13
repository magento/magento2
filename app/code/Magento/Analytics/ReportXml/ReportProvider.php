<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\Api\SearchCriteria;

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
            $this->currentPosition = 0;
            $this->connection = $this->connectionFactory->getConnection($this->dataSelect->getConnectionName());
            $this->countTotal = $this->connection->fetchOne($this->dataSelect->getSelectCountSql());
        }

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
