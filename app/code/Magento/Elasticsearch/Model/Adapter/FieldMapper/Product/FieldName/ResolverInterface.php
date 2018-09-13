<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName;

interface ResolverInterface
{
    /**
     * Get field name.
     *
     * @param $attributeCode
     * @param array $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = []);
}
