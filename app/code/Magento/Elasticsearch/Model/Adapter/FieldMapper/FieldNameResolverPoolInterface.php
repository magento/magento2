<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;

/**
 * Identify field name resolver for attribute.
 */
interface FieldNameResolverPoolInterface
{
    /**
     * Get field name resolver.
     *
     * @param string $attributeCode
     * @param array $context
     * @return ResolverInterface
     */
    public function getResolver(string $attributeCode, $context = []) : ResolverInterface;
}
