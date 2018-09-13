<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName;

use Magento\Framework\Exception\NotFoundException;

/**
 * Resolve name of type specification.
 */
interface SpecificationInterface
{
    /**
     * Get specification name.
     *
     * @param string $attributeCode
     * @return string
     */
    public function resolve(string $attributeCode): string;

    /**
     * Get next specification.
     *
     * @return SpecificationInterface
     * @throws NotFoundException
     */
    public function getNext(): SpecificationInterface;

    /**
     * Check if next specification present.
     *
     * @return bool
     */
    public function hasNext(): bool;
}
