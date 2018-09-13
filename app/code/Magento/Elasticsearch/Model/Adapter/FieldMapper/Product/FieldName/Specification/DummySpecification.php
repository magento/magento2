<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Specification;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\SpecificationInterface;

/**
 * Dummy specification.
 */
class DummySpecification extends Specification implements SpecificationInterface
{
    const TYPE = 'DUMMY';

    /**
     * {@inheritdoc}
     */
    public function resolve(string $attributeCode): string
    {
        return self::TYPE;
    }
}
