<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\DB;

use Magento\Analytics\ReportXml\ConnectionFactory;
use Magento\Analytics\ReportXml\QueryFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class ReportValidator
 *
 * Validates report definitions by doing query to storage with limit 0
 */
class ReportValidator
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * ReportValidator constructor.
     *
     * Needs connection and query factory for do a query
     *
     * @param ConnectionFactory $connectionFactory
     * @param QueryFactory $queryFactory
     */
    public function __construct(ConnectionFactory $connectionFactory, QueryFactory $queryFactory)
    {
        $this->connectionFactory = $connectionFactory;
        $this->queryFactory = $queryFactory;
    }

    /**
     * Tries to do query for provided report with limit 0 and return error information if it failed
     *
     * @param string $name
     * @param SearchCriteriaInterface $criteria
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($name, SearchCriteriaInterface $criteria = null)
    {
        $query = $this->queryFactory->create($name);
        $connection = $this->connectionFactory->getConnection($query->getConnectionName());
        $query->getSelect()->limit(0);
        try {
            $connection->query($query->getSelect());
        } catch (\Zend_Db_Statement_Exception $e) {
            return [$name, $e->getMessage()];
        }

        return [];
    }
}
