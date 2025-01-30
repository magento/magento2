<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use Magento\Framework\App\State\ReloadProcessorInterface;

/**
 * Wrapper for GraphQl query parser. It parses query string into a `GraphQL\Language\AST\DocumentNode`
 */
class QueryParser implements ReloadProcessorInterface
{
    /**
     * @var string[]
     */
    private $parsedQueries = [];

    /**
     * Parse query string into a `GraphQL\Language\AST\DocumentNode`.
     *
     * @param string $query
     * @return DocumentNode
     * @throws \GraphQL\Error\SyntaxError
     */
    public function parse(string $query): DocumentNode
    {
        $cacheKey = sha1($query);
        if (!isset($this->parsedQueries[$cacheKey])) {
            $this->parsedQueries[$cacheKey] = Parser::parse(new Source($query, 'GraphQL'));
        }
        return $this->parsedQueries[$cacheKey];
    }

    /**
     * @inheritDoc
     */
    public function reloadState(): void
    {
        $this->parsedQueries = [];
    }
}
