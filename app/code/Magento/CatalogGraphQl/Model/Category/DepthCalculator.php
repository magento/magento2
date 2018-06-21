<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use GraphQL\Language\AST\FieldNode;

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
            $childrenDepth[] = $this->calculate($node);
        }

        return $depth + max($childrenDepth);
    }
}
