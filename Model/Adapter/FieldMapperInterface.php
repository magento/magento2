<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

interface FieldMapperInterface
{
    /**#@+
     * Text flags for field mapping context
     */
    const TYPE_QUERY = 'text';
    const TYPE_SORT = 'sort';
    const TYPE_FILTER = 'default';
    /**#@-*/

    /**
     * Get field name
     *
     * @param string $attributeCode
     * @param array $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = []);

    /**
     * Get all entity attribute types
     *
     * @param array $context
     * @return array
     */
    public function getAllAttributesTypes($context = []);
}
