<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Specification;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\SpecificationInterface;

/**
 * Specification for the price attribute.
 */
class PriceAttribute extends Specification implements SpecificationInterface
{
    const TYPE = 'PRICE_ATTRIBUTE';

    /**
     * {@inheritdoc}
     */
    public function resolve(string $attributeCode): string
    {
        if ($attributeCode === 'price') {
            return self::TYPE;
        }

        return $this->getNext()->resolve($attributeCode);
    }
}
