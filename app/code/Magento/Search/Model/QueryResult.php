<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @api
 * @since 100.0.2
 */
class QueryResult
{
    /**
     * @param string $queryText
     * @param string|int $resultsCount
     */
    public function __construct(
        private $queryText,
        private $resultsCount
    ) {
    }

    /**
     * @return string
     */
    public function getQueryText()
    {
        return $this->queryText;
    }

    /**
     * @return int
     */
    public function getResultsCount()
    {
        return $this->resultsCount;
    }
}
