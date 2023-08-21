<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\Framework\GraphQl\Query\FieldTranslator;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Select Product Fields From Resolve Info
 */
class ProductFieldsSelector
{
    /**
     * @var FieldTranslator
     */
    private FieldTranslator $fieldTranslator;

    /**
     * @var AttributesJoiner
     */
    private AttributesJoiner $attributesJoiner;

    /**
     * @param FieldTranslator $fieldTranslator
     * @param AttributesJoiner $attributesJoiner
     */
    public function __construct(
        FieldTranslator $fieldTranslator,
        AttributesJoiner $attributesJoiner
    ) {
        $this->fieldTranslator = $fieldTranslator;
        $this->attributesJoiner = $attributesJoiner;
    }

    /**
     * Return field names for all requested product fields.
     *
     * @param ResolveInfo $info
     * @param string $productNodeName
     * @return string[]
     */
    public function getProductFieldsFromInfo(ResolveInfo $info, string $productNodeName = 'product'): array
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            if ($node->name->value !== $productNodeName) {
                continue;
            }
                $queryFields = $this->attributesJoiner->getQueryFields($node, $info);
                $fieldNames[] = $queryFields;
        }

        return array_merge(...$fieldNames);
    }
}
