<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

interface FieldMapperInterface
{
    /**
     * Get field name
     *
     * @param string $attributeCode
     * @param array $context
     * @return mixed
     */
    public function getFieldName($attributeCode, $context = []);
}
