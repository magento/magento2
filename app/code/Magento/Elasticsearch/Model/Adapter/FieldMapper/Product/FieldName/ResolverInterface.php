<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName;

use Magento\Framework\Exception\NotFoundException;

/**
 * Field name resolver interface.
 */
interface ResolverInterface
{
    /**
     * Get field name.
     *
     * @param $attributeCode
     * @param array $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = []): string;

    /**
     * Get next resolver.
     *
     * @return ResolverInterface
     * @throws NotFoundException
     */
    public function getNext(): ResolverInterface;

    /**
     * Check if next resolver present.
     *
     * @return bool
     */
    public function hasNext(): bool;
}
