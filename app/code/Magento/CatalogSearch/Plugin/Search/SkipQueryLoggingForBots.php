<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Plugin\Search;

use Magento\Customer\Model\Visitor;
use Magento\Search\Model\Query;

/**
 * Prevents bots and other ignored user agents from polluting search term statistics (popularity, results count)
 */
class SkipQueryLoggingForBots
{
    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Skip incrementing query popularity for ignored user agents
     *
     * @param Query $subject
     * @param callable $proceed
     * @return Query
     */
    public function aroundSaveIncrementalPopularity(Query $subject, callable $proceed)
    {
        if ($this->visitor->isRequestLoggingSkipped()) {
            return $subject;
        }

        return $proceed();
    }

    /**
     * Skip saving query results count for ignored user agents
     *
     * @param Query $subject
     * @param callable $proceed
     * @param int $numResults
     * @return Query
     */
    public function aroundSaveNumResults(Query $subject, callable $proceed, $numResults)
    {
        if ($this->visitor->isRequestLoggingSkipped()) {
            return $subject->setNumResults($numResults);
        }

        return $proceed($numResults);
    }
}
