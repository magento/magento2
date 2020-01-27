<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

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
        $productFields = $resolveInfo->getFieldSelection(1);
        $sectionNames = ['items', 'product'];

        $fieldNames = [];
        foreach ($sectionNames as $sectionName) {
            if (isset($productFields[$sectionName])) {
                foreach (array_keys($productFields[$sectionName]) as $fieldName) {
                    $fieldNames[] = $this->fieldTranslator->translate($fieldName);
                }
            }
        }

        return array_unique($fieldNames);
    }
}
