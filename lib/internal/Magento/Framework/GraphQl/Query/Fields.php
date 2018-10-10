<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;

/**
 * This class holds a list of all queried fields and is used to enable performance optimization for schema loading.
 */
class Fields
{
    /**
     * @var string[]
     */
    private $fieldsUsedInQuery = [];

    /**
     * Set Query for extracting list of fields.
     *
     * @param string $query
     * @return void
     */
    public function setQuery($query)
    {
        $queryFields = [];
        try {
            $queryAst = \GraphQL\Language\Parser::parse(new \GraphQL\Language\Source($query ?: '', 'GraphQL'));
            \GraphQL\Language\Visitor::visit(
                $queryAst,
                [
                    'leave' => [
                        NodeKind::NAME => function (Node $node) use (&$queryFields) {
                            $queryFields[$node->value] = $node->value;
                        }
                    ]
                ]
            );
        } catch (\Exception $e) {
            // If a syntax error is encountered do not collect fields
        }
        if (isset($queryFields['IntrospectionQuery'])) {
            // It must be possible to query any fields during introspection query
            $queryFields = [];
        }
        $this->fieldsUsedInQuery = $queryFields;
    }

    /**
     * Get list of fields used in GraphQL query.
     *
     * This method is stateful and relies on the query being set with setQuery.
     *
     * @return string[]
     */
    public function getFieldsUsedInQuery()
    {
        return $this->fieldsUsedInQuery;
    }
}
