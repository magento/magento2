<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Language\Visitor;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryDepth;
use GraphQL\Validator\Rules\QueryComplexity;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * QueryComplexityLimiter
 *
 * Sets limits for query complexity. A single GraphQL query can potentially
 * generate thousands of database operations so, the very complex queries
 * should be filtered and rejected.
 *
 * https://github.com/webonyx/graphql-php/blob/master/docs/security.md#query-complexity-analysis
 */
class QueryComplexityLimiter
{
    /**
     * @var int
     */
    private $queryDepth;

    /**
     * @var int
     */
    private $queryComplexity;

    /**
     * @var IntrospectionConfiguration
     */
    private $introspectionConfig;

    /**
     * @param int $queryDepth
     * @param int $queryComplexity
     * @param IntrospectionConfiguration $introspectionConfig
     */
    public function __construct(
        int $queryDepth,
        int $queryComplexity,
        IntrospectionConfiguration $introspectionConfig
    ) {
        $this->queryDepth = $queryDepth;
        $this->queryComplexity = $queryComplexity;
        $this->introspectionConfig = $introspectionConfig;
    }

    /**
     * Sets limits for query complexity
     *
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(): void
    {
        DocumentValidator::addRule(new QueryComplexity($this->queryComplexity));
        DocumentValidator::addRule(
            new DisableIntrospection((int) $this->introspectionConfig->isIntrospectionDisabled())
        );
        DocumentValidator::addRule(new QueryDepth($this->queryDepth));
    }

    /**
     * Performs a preliminary field count check before performing more extensive query validation.
     *
     * This is necessary for performance optimization, as extremely large queries require a substantial
     * amount of time to fully validate and can affect server performance.
     *
     * @param string $query
     * @throws GraphQlInputException
     */
    public function validateFieldCount(string $query): void
    {
        if (!empty($query)) {
            $totalFieldCount = 0;
            $queryAst = Parser::parse(new Source($query ?: '', 'GraphQL'));
            Visitor::visit(
                $queryAst,
                [
                    'leave' => [
                        NodeKind::FIELD => function (Node $node) use (&$totalFieldCount) {
                            $totalFieldCount++;
                        }
                    ]
                ]
            );

            if ($totalFieldCount > $this->queryComplexity) {
                throw new GraphQlInputException(__(
                    'Max query complexity should be %1 but got %2.',
                    $this->queryComplexity,
                    $totalFieldCount
                ));
            }
        }
    }
}
