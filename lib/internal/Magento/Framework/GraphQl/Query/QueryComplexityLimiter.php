<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\Visitor;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryDepth;
use GraphQL\Validator\Rules\QueryComplexity;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * QueryComplexityLimiter
 *
 * Sets limits for query complexity. A single GraphQL query can potentially
 * generate thousands of database operations so, the very complex queries
 * should be filtered and rejected.
 *
 * https://github.com/webonyx/graphql-php/blob/master/docs/security.md#query-complexity-analysis
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var QueryParser
     */
    private $queryParser;

    /**
     * @var MaximumAliasConfiguration
     */
    private $maximumAliasConfiguration;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * Constructor
     *
     * @param int $queryDepth
     * @param int $queryComplexity
     * @param IntrospectionConfiguration $introspectionConfig
     * @param QueryParser|null $queryParser
     * @param MaximumAliasConfiguration|null $maximumAliasConfiguration
     */
    public function __construct(
        int $queryDepth,
        int $queryComplexity,
        IntrospectionConfiguration $introspectionConfig,
        ?QueryParser $queryParser = null,
        ?MaximumAliasConfiguration $maximumAliasConfiguration = null
    ) {
        $this->queryDepth = $queryDepth;
        $this->queryComplexity = $queryComplexity;
        $this->introspectionConfig = $introspectionConfig;
        $this->queryParser = $queryParser ?: ObjectManager::getInstance()->get(QueryParser::class);
        $this->maximumAliasConfiguration = $maximumAliasConfiguration ?:
            ObjectManager::getInstance()->get(MaximumAliasConfiguration::class);
    }

    /**
     * Get rules
     *
     * @return array
     */
    private function getRules()
    {
        if (empty($this->rules)) {
            $this->rules[] = new QueryComplexity($this->queryComplexity);
            $this->rules[] = new DisableIntrospection((int) $this->introspectionConfig->isIntrospectionDisabled());
            $this->rules[] = new QueryDepth($this->queryDepth);
        }
        return $this->rules;
    }
    /**
     * Sets limits for query complexity
     *
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(): void
    {
        foreach ($this->getRules() as $rule) {
            DocumentValidator::addRule($rule);
        }
    }

    /**
     * Performs a preliminary field count check before performing more extensive query validation.
     *
     * This is necessary for performance optimization, as extremely large queries require a substantial
     * amount of time to fully validate and can affect server performance.
     *
     * @param DocumentNode|string $query
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateFieldCount(DocumentNode|string $query): void
    {
        if (!empty($query)) {
            $totalFieldCount = 0;
            if (is_string($query)) {
                $query = $this->queryParser->parse($query);
            }

            Visitor::visit(
                $query,
                [
                    'leave' => [
                        NodeKind::FIELD => function () use (&$totalFieldCount) {
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

    /**
     * Performs a preliminary Alias count check before performing more extensive query validation.
     *
     * This is necessary for performance optimization, as extremely large number of alias in a request
     * require a substantial amount of resource can affect server performance.
     *
     * @param DocumentNode $query
     * @return void
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateAliasCount(DocumentNode $query): void
    {
        if ($this->maximumAliasConfiguration->isMaximumAliasLimitEnabled()) {
            $aliasCount = 0;
            foreach ($query->definitions as $definition) {
                if (property_exists($definition, 'selectionSet')) {
                    $aliasCount += $this->countAliasesInSelectionSet($definition->selectionSet);
                }
            }
            $allowedAliasCount = $this->maximumAliasConfiguration->getMaximumAliasAllowed();
            if ($aliasCount > $allowedAliasCount) {
                throw new GraphQlInputException(__(
                    'Max Aliases in query should be %1 but got %2.',
                    $allowedAliasCount,
                    $aliasCount
                ));
            }
        }
    }

    /**
     * Performs counting of aliases in a graphql request
     *
     * @param SelectionSetNode $selectionSet
     * @return int
     */
    private function countAliasesInSelectionSet(SelectionSetNode $selectionSet): int
    {
        if ($selectionSet === null) {
            return 0;
        }

        $aliasCount = 0;

        foreach ($selectionSet->selections as $selection) {
            if ($selection instanceof FieldNode) {
                if ($selection->alias !== null) {
                    $aliasCount++;
                }

                if ($selection->selectionSet !== null) {
                    $aliasCount += $this->countAliasesInSelectionSet($selection->selectionSet);
                }
            }
        }

        return $aliasCount;
    }
}
