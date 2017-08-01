<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\Api\SearchCriteria;

/**
 * Class ReportProvider
 *
 * Providers for reports data
 * @since 2.2.0
 */
class ReportProvider
{
    /**
     * @var QueryFactory
     * @since 2.2.0
     */
    private $queryFactory;

    /**
     * @var ConnectionFactory
     * @since 2.2.0
     */
    private $connectionFactory;

    /**
     * @var IteratorFactory
     * @since 2.2.0
     */
    private $iteratorFactory;

    /**
     * ReportProvider constructor.
     *
     * @param QueryFactory $queryFactory
     * @param ConnectionFactory $connectionFactory
     * @param IteratorFactory $iteratorFactory
     * @since 2.2.0
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
     * Returns custom iterator name for report
     * Null for default
     *
     * @param Query $query
     * @return string|null
     * @since 2.2.0
     */
    private function getIteratorName(Query $query)
    {
        $config = $query->getConfig();
        return isset($config['iterator']) ? $config['iterator'] : null;
    }

    /**
     * Returns report data by name and criteria
     *
     * @param string $name
     * @return \IteratorIterator
     * @since 2.2.0
     */
    public function getReport($name)
    {
        $query = $this->queryFactory->create($name);
        $connection = $this->connectionFactory->getConnection($query->getConnectionName());
        $statement = $connection->query($query->getSelect());
        return $this->iteratorFactory->create($statement, $this->getIteratorName($query));
    }
}
