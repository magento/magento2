<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Language\Visitor;
use GraphQL\Language\AST\SelectionSetNode;
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
     * @var MaximumAliasConfiguration
     */
    private $maximumAliasConfiguration;

    /**
     * @param int $queryDepth
     * @param int $queryComplexity
     * @param IntrospectionConfiguration $introspectionConfig
     * @param MaximumAliasConfiguration|null $maximumAliasConfiguration
     */
    public function __construct(
        int $queryDepth,
        int $queryComplexity,
        IntrospectionConfiguration $introspectionConfig,
        MaximumAliasConfiguration $maximumAliasConfiguration = null
    ) {
        $this->queryDepth = $queryDepth;
        $this->queryComplexity = $queryComplexity;
        $this->introspectionConfig = $introspectionConfig;
        $this->maximumAliasConfiguration = $maximumAliasConfiguration ?:
            ObjectManager::getInstance()->get(MaximumAliasConfiguration::class);
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
     * @param string $query
     * @return void
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateAliasCount(string $query): void
    {
        if ($this->maximumAliasConfiguration->isMaximumAliasLimitEnabled()) {
            $aliasCount = 0;
            $query = Parser::parse(new Source($query ?: '', 'GraphQL'));
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
