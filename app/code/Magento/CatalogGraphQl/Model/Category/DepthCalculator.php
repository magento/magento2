<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\NodeKind;
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
        $selections = $fieldNode->selectionSet->selections ?? [];
        $depth = count($selections) ? 1 : 0;
        $childrenDepth = [0];
        foreach ($selections as $node) {
            if (isset($node->alias) && null !== $node->alias) {
                continue;
            }

            if ($node->kind ===  NodeKind::INLINE_FRAGMENT) {
                $childrenDepth[] = $this->addInlineFragmentDepth($resolveInfo, $node);
            } elseif ($node->kind === NodeKind::FRAGMENT_SPREAD && isset($resolveInfo->fragments[$node->name->value])) {
                foreach ($resolveInfo->fragments[$node->name->value]->selectionSet->selections as $spreadNode) {
                    $childrenDepth[] = $this->calculate($resolveInfo, $spreadNode);
                }
            } else {
                $childrenDepth[] = $this->calculate($resolveInfo, $node);
            }
        }

        return $depth + max($childrenDepth);
    }

    /**
     * Add inline fragment fields into calculating of category depth
     *
     * @param ResolveInfo $resolveInfo
     * @param InlineFragmentNode $inlineFragmentField
     * @param array $depth
     * @return int
     */
    private function addInlineFragmentDepth(
        ResolveInfo $resolveInfo,
        InlineFragmentNode $inlineFragmentField,
        $depth = []
    ): int {
        $selections = $inlineFragmentField->selectionSet->selections;
        /** @var FieldNode $field */
        foreach ($selections as $field) {
            if ($field->kind === NodeKind::INLINE_FRAGMENT) {
                $depth[] = $this->addInlineFragmentDepth($resolveInfo, $field, $depth);
            } elseif ($field->selectionSet && $field->selectionSet->selections) {
                $depth[] = $this->calculate($resolveInfo, $field);
            }
        }

        return $depth ? max($depth) : 0;
    }
}
