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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Wrapper for GraphQl query parser. It parses query string into a `GraphQL\Language\AST\DocumentNode`
 */
class QueryParser implements ReloadProcessorInterface
{
    private const DEFAULT_MAX_NESTING_DEPTH = 200;

    /**
     * @var DocumentNode[]
     */
    private array $parsedQueries = [];

    /**
     * @param int $maxNestingDepth
     */
    public function __construct(
        private readonly int $maxNestingDepth = self::DEFAULT_MAX_NESTING_DEPTH
    ) {
    }

    /**
     * Parse query string into a `GraphQL\Language\AST\DocumentNode`.
     *
     * @param string $query
     * @return DocumentNode
     * @throws GraphQlInputException
     * @throws \GraphQL\Error\SyntaxError
     */
    public function parse(string $query): DocumentNode
    {
        $cacheKey = sha1($query);
        if (!isset($this->parsedQueries[$cacheKey])) {
            $this->validateNestingDepth($query);
            $this->parsedQueries[$cacheKey] = Parser::parse(new Source($query, 'GraphQL'));
        }
        return $this->parsedQueries[$cacheKey];
    }

    /**
     * Validates that the query does not exceed the maximum allowed nesting depth.
     *
     * This check runs before the recursive parser to prevent stack overflow from
     * deeply nested inline fragments or selection sets.
     *
     * @param string $query
     * @throws GraphQlInputException
     */
    private function validateNestingDepth(string $query): void
    {
        /** @var array{depth: int, maxDepth: int, inString: bool, escaped: bool} $state */
        $state = [
            'depth' => 0,
            'maxDepth' => 0,
            'inString' => false,
            'escaped' => false,
        ];
        for ($i = 0, $len = strlen($query); $i < $len; $i++) {
            $char = $query[$i];
            if ($this->shouldSkipAfterEscape($char, $state)) {
                continue;
            }
            if ($this->shouldToggleStringOnQuote($char, $state)) {
                continue;
            }
            if ($state['inString']) {
                continue;
            }
            $this->updateDepthForBracket($char, $state);
            $this->throwIfQueryTooDeep($state['maxDepth']);
        }
    }

    /**
     * Determine if the current character was consumed as part of escape handling.
     *
     * @param string $char
     * @param array $state Keys: depth, maxDepth, inString, escaped
     * @return bool
     */
    private function shouldSkipAfterEscape(string $char, array &$state): bool
    {
        if ($state['escaped']) {
            $state['escaped'] = false;
            return true;
        }
        if ($char === '\\' && $state['inString']) {
            $state['escaped'] = true;
            return true;
        }
        return false;
    }

    /**
     * Toggle string-literal mode when a double quote is found outside of escapes.
     *
     * @param string $char
     * @param array $state Keys: depth, maxDepth, inString, escaped
     * @return bool
     */
    private function shouldToggleStringOnQuote(string $char, array &$state): bool
    {
        if ($char !== '"') {
            return false;
        }
        $state['inString'] = !$state['inString'];
        return true;
    }

    /**
     * Update nesting depth when a bracket character is seen outside of strings.
     *
     * @param string $char
     * @param array $state Keys: depth, maxDepth, inString, escaped
     * @return void
     */
    private function updateDepthForBracket(string $char, array &$state): void
    {
        if ($char === '{' || $char === '[') {
            $state['depth']++;
            if ($state['depth'] > $state['maxDepth']) {
                $state['maxDepth'] = $state['depth'];
            }
            return;
        }
        if ($char === '}' || $char === ']') {
            $state['depth']--;
        }
    }

    /**
     * Fail fast when maximum nesting depth exceeds the configured limit.
     *
     * @param int $maxDepth
     * @return void
     * @throws GraphQlInputException
     */
    private function throwIfQueryTooDeep(int $maxDepth): void
    {
        if ($maxDepth > $this->maxNestingDepth) {
            throw new GraphQlInputException(
                __('Query is too deep. Reduce the nesting level of your query.')
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function reloadState(): void
    {
        $this->parsedQueries = [];
    }
}
