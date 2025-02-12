<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\AdvancedSearch\Model\SuggestedQueries;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CatalogGraphQl\Model\QueryProcessor;

/**
 * Search suggestions implementations for GraphQL
 */
class Suggestions
{
    /**
     * @var QueryProcessor
     */
    private $queryProcessor;

    /**
     * @var SuggestedQueries
     */
    private $suggestedQueries;

    /**
     * @param QueryProcessor $queryProcessor
     * @param SuggestedQueries $suggestedQueries
     */
    public function __construct(
        QueryProcessor $queryProcessor,
        SuggestedQueries $suggestedQueries
    ) {
        $this->queryProcessor = $queryProcessor;
        $this->suggestedQueries = $suggestedQueries;
    }

    /**
     * Return search suggestions for the provided query text
     *
     * @param ContextInterface $context
     * @param string $queryText
     * @return array
     */
    public function execute(ContextInterface $context, string $queryText) : array
    {
        $result = [];
        $query = $this->queryProcessor->prepare($context, $queryText);
        $suggestionItems = $this->suggestedQueries->getItems($query);
        foreach ($suggestionItems as $suggestion) {
            $result[] = ['search' => $suggestion->getQueryText()];
        }
        return $result;
    }
}
