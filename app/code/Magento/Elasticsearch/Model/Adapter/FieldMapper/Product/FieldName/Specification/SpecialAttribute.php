<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Specification;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\SpecificationInterface;

/**
 * Specification for the special attributes.
 */
class SpecialAttribute extends Specification implements SpecificationInterface
{
    const TYPE = 'SPECIAL_ATTRIBUTE';

    /**
     * {@inheritdoc}
     */
    public function resolve(string $attributeCode): string
    {
        if (in_array($attributeCode, ['id', 'sku', 'store_id', 'visibility'], true)) {
            return self::TYPE;
        }

        return $this->getNext()->resolve($attributeCode);
    }
}
