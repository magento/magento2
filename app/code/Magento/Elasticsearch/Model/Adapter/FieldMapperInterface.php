<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

/**
 * @api
 * @since 100.1.0
 */
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
     * @since 100.1.0
     */
    public function getFieldName($attributeCode, $context = []);

    /**
     * Get all entity attribute types
     *
     * @param array $context
     * @return array
     * @since 100.1.0
     */
    public function getAllAttributesTypes($context = []);
}
