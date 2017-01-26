<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

/**
 * Class ReportProvider
 *
 * Providers for reports data
 */
class ReportProvider
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    public function __construct(
        QueryFactory $queryFactory,
        ConnectionFactory $connectionFactory
    ) {
        $this->queryFactory = $queryFactory;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @param string $name
     * @return \IteratorIterator
     */
    public function getReport($name)
    {
        $query = $this->queryFactory->create($name);
        $connection = $this->connectionFactory->getConnection($query->getConnectionName());
        $statement = $connection->query($query->getQueryString());
        return new \IteratorIterator($statement);

    }
}
