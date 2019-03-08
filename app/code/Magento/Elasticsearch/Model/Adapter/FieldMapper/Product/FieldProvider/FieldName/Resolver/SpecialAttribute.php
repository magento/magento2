<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Resolver field name for not special attribute.
 */
class SpecialAttribute implements ResolverInterface
{
    /**
     * Get field name for special list of attributes.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if (in_array($attribute->getAttributeCode(), ['id', 'sku', 'store_id', 'visibility'], true)
            && empty($context['type'])
        ) {
            return $attribute->getAttributeCode();
        }

        return null;
    }
}
