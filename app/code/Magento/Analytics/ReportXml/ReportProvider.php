<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use \Magento\Framework\App\ResourceConnection;

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
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        QueryFactory $queryFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->queryFactory = $queryFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string $name
     * @return \IteratorIterator
     */
    public function getReport($name)
    {
        $query = $this->queryFactory->create($name);
        $connection = $this->resourceConnection->getConnectionByName($query->getConnectionName());
        $statement = $connection->query($query->getQueryString());
        return new \IteratorIterator($statement);

    }
}
