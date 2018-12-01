<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryDepth;
use GraphQL\Validator\Rules\QueryComplexity;

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
     * @param int $queryDepth
     * @param int $queryComplexity
     */
    public function __construct(
        int $queryDepth,
        int $queryComplexity
    ) {
        $this->queryDepth = $queryDepth;
        $this->queryComplexity = $queryComplexity;
    }

    /**
     * Sets limits for query complexity
     *
     * @return void
     */
    public function execute(): void
    {
        DocumentValidator::addRule(new QueryComplexity($this->queryComplexity));
        DocumentValidator::addRule(new DisableIntrospection());
        DocumentValidator::addRule(new QueryDepth($this->queryDepth));
    }
}
