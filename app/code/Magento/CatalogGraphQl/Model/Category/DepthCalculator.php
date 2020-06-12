<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\InlineFragmentNode;

/**
 * Used for determining the depth information for a requested category tree in a GraphQL request
 */
class DepthCalculator
{
    /**
     * Calculate the total depth of a category tree inside a GraphQL request
     *
     * @param FieldNode $fieldNode
     * @return int
     */
    public function calculate(FieldNode $fieldNode) : int
    {
        $selections = $fieldNode->selectionSet->selections ?? [];
        $depth = count($selections) ? 1 : 0;
        $childrenDepth = [0];
        foreach ($selections as $node) {
            if ($node->kind === 'InlineFragment' || null !== $node->alias) {
                $childrenDepth[] = $this->addInlineFragmentDepth($node);
            } else {
                $childrenDepth[] = $this->calculate($node);
            }
        }

        return $depth + max($childrenDepth);
    }

    /**
     * Add inline fragment fields into calculating of category depth
     *
     * @param InlineFragmentNode $inlineFraggmentField
     * @param array $depth
     * @return int[]
     */
    private function addInlineFragmentDepth(InlineFragmentNode $inlineFraggmentField, $depth = [])
    {
        $selections = $inlineFraggmentField->selectionSet->selections;
        /** @var FieldNode $field */
        foreach ($selections as $field) {
            if ($field->kind === 'InlineFragment') {
                $depth[] = $this->addInlineFragmentDepth($field, $depth);
            } elseif ($field->selectionSet && $field->selectionSet->selections) {
                $depth[] = $this->calculate($field);
            }
        }

        return $depth ? max($depth) : 0;
    }
}
