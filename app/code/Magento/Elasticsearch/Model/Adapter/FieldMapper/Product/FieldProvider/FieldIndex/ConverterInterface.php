<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

/**
 * Field type converter from internal index value to elastic service.
 */
interface ConverterInterface
{
    /**#@+
     * Text flags for internal no index value.
     */
    public const INTERNAL_NO_INDEX_VALUE = 'no_index';
    public const INTERNAL_INDEX_VALUE = 'index';

    /**
     * Get service field type.
     *
     * @param string $internalType
     * @return string|boolean
     */
    public function convert(string $internalType);
}
