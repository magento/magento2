<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Search\Model;

class QueryResult
{
    /**
     * @var string
     */
    private $queryText;

    /**
     * @var int
     */
    private $resultsCount;

    /**
     * @param string $queryText
     * @param string $resultsCount
     */
    public function __construct($queryText, $resultsCount)
    {
        $this->queryText = $queryText;
        $this->resultsCount = $resultsCount;
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
