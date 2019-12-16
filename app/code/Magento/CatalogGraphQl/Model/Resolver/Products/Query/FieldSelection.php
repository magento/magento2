<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use GraphQL\Language\AST\SelectionNode;
use Magento\Framework\GraphQl\Query\FieldTranslator;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Extract requested fields from products query
 */
class FieldSelection
{
    /**
     * @var FieldTranslator
     */
    private $fieldTranslator;

    /**
     * @param FieldTranslator $fieldTranslator
     */
    public function __construct(FieldTranslator $fieldTranslator)
    {
        $this->fieldTranslator = $fieldTranslator;
    }

    /**
     * Get requested fields from products query
     *
     * @param ResolveInfo $resolveInfo
     * @return string[]
     */
    public function getProductsFieldSelection(ResolveInfo $resolveInfo): array
    {
        return $this->getProductFields($resolveInfo);
    }

    /**
     * Return field names for all requested product fields.
     *
     * @param ResolveInfo $info
     * @return string[]
     */
    private function getProductFields(ResolveInfo $info): array
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            if ($node->name->value !== 'products' && $node->name->value !== 'variants') {
                continue;
            }
            foreach ($node->selectionSet->selections as $selection) {
                if ($selection->name->value !== 'items' && $selection->name->value !== 'product') {
                    continue;
                }
                $fieldNames[] = $this->collectProductFieldNames($selection, $fieldNames);
            }
        }
        if (!empty($fieldNames)) {
            $fieldNames = array_merge(...$fieldNames);
        }
        return $fieldNames;
    }

    /**
     * Collect field names for each node in selection
     *
     * @param SelectionNode $selection
     * @param array $fieldNames
     * @return array
     */
    private function collectProductFieldNames(SelectionNode $selection, array $fieldNames = []): array
    {
        foreach ($selection->selectionSet->selections as $itemSelection) {
            if ($itemSelection->kind === 'InlineFragment') {
                foreach ($itemSelection->selectionSet->selections as $inlineSelection) {
                    if ($inlineSelection->kind === 'InlineFragment') {
                        continue;
                    }
                    $fieldNames[] = $this->fieldTranslator->translate($inlineSelection->name->value);
                }
                continue;
            }
            $fieldNames[] = $this->fieldTranslator->translate($itemSelection->name->value);
        }

        return $fieldNames;
    }
}
