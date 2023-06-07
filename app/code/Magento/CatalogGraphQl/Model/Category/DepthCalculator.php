<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\SelectionNode;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Used for determining the depth information for a requested category tree in a GraphQL request
 */
class DepthCalculator
{
    /**
     * Calculate the total depth of a category tree inside a GraphQL request
     *
     * @param ResolveInfo $resolveInfo
     * @param FieldNode $fieldNode
     * @return int
     */
    public function calculate(ResolveInfo $resolveInfo, FieldNode $fieldNode) : int
    {
        return $this->calculateRecursive($resolveInfo, $fieldNode);
    }

    /**
     * Calculate recursive the total depth of a category tree inside a GraphQL request
     *
     * @param ResolveInfo $resolveInfo
     * @param Node $node
     * @return int
     */
    private function calculateRecursive(ResolveInfo $resolveInfo, Node $node) : int
    {
        if ($node->kind === NodeKind::FRAGMENT_SPREAD) {
            $selections = isset($resolveInfo->fragments[$node->name->value]) ?
                $resolveInfo->fragments[$node->name->value]->selectionSet->selections : [];
        } else {
            $selections = $node->selectionSet->selections ?? [];
        }
        $depth = count($selections) ? 1 : 0;
        $childrenDepth = [0];
        foreach ($selections as $subNode) {
            if (isset($subNode->alias) && null !== $subNode->alias) {
                continue;
            }

            if ($subNode->kind ===  NodeKind::INLINE_FRAGMENT) {
                $childrenDepth[] = $this->addInlineFragmentDepth($resolveInfo, $subNode);
            } else {
                $childrenDepth[] = $this->calculateRecursive($resolveInfo, $subNode);
            }
        }

        return $depth + max($childrenDepth);
    }

    /**
     * Add inline fragment fields into calculating of category depth
     *
     * @param ResolveInfo $resolveInfo
     * @param SelectionNode $inlineFragmentField
     * @param array $depth
     * @return int
     */
    private function addInlineFragmentDepth(
        ResolveInfo $resolveInfo,
        SelectionNode $inlineFragmentField,
        $depth = []
    ): int {
        $selections = $inlineFragmentField->selectionSet->selections;
        /** @var FieldNode $field */
        foreach ($selections as $field) {
            if ($field->kind === NodeKind::INLINE_FRAGMENT) {
                $depth[] = $this->addInlineFragmentDepth($resolveInfo, $field, $depth);
            } elseif (!empty($field->selectionSet) && $field->selectionSet->selections) {
                $depth[] = $this->calculate($resolveInfo, $field);
            }
        }

        return $depth ? max($depth) : 0;
    }
}
