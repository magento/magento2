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
 * Sets limits for query complexity. A single GraphQL query can potentially
 * generate thousands of database operations so, the very complex queries
 * should be filtered and rejected.
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
        int $queryDepth = 50,
        int $queryComplexity = 150
    ) {
        $this->queryDepth = $queryDepth;
        $this->queryComplexity = $queryComplexity;
    }

    public function execute(bool $disableIntrospection = false): void
    {
        DocumentValidator::addRule(new QueryDepth($this->queryDepth));
        DocumentValidator::addRule(new QueryComplexity($this->queryComplexity));

        if ($disableIntrospection) {
            DocumentValidator::addRule(new DisableIntrospection());
        }
    }
}
