<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Specification;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\SpecificationInterface;

/**
 * Specification for the category name attribute.
 */
class CategoryNameAttribute extends Specification implements SpecificationInterface
{
    const TYPE = 'CATEGORY_NAME_ATTRIBUTE';

    /**
     * {@inheritdoc}
     */
    public function resolve(string $attributeCode): string
    {
        if ($attributeCode === 'category_name') {
            return self::TYPE;
        }

        return $this->getNext()->resolve($attributeCode);
    }
}
