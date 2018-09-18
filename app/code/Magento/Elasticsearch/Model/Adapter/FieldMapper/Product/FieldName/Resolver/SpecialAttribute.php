<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;

/**
 * Resolver field name for not special attribute.
 */
class SpecialAttribute extends Resolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = []): string
    {
        if (in_array($attributeCode, ['id', 'sku', 'store_id', 'visibility'], true)) {
            return $attributeCode;
        }

        return $this->getNext()->getFieldName($attributeCode, $context);
    }
}
