<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * @api
 * @since 2.0.0
 */
class QueryResult
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $queryText;

    /**
     * @var int
     * @since 2.0.0
     */
    private $resultsCount;

    /**
     * @param string $queryText
     * @param string $resultsCount
     * @since 2.0.0
     */
    public function __construct($queryText, $resultsCount)
    {
        $this->queryText = $queryText;
        $this->resultsCount = $resultsCount;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getQueryText()
    {
        return $this->queryText;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getResultsCount()
    {
        return $this->resultsCount;
    }
}
