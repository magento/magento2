<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;

/**
 * Field index type resolver interface.
 *
 * @api
 */
interface ResolverInterface
{
    /**
     * Get field index.
     *
     * @param AttributeAdapter $attribute
     * @return string|boolean
     */
    public function getFieldIndex(AttributeAdapter $attribute);
}
