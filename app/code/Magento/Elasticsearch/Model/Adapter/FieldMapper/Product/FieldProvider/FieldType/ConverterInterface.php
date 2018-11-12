<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType;

/**
 * @api
 * Field type converter from internal data types to elastic service.
 */
interface ConverterInterface
{
    /**#@+
     * Text flags for internal field types
     */
    public const INTERNAL_DATA_TYPE_STRING = 'string';
    public const INTERNAL_DATA_TYPE_FLOAT = 'float';
    public const INTERNAL_DATA_TYPE_INT = 'integer';
    public const INTERNAL_DATA_TYPE_DATE = 'date';
    public const INTERNAL_DATA_TYPE_KEYWORD = 'keyword';
    /**#@-*/

    /**
     * Get service field type.
     *
     * @param string $internalType
     * @return string
     */
    public function convert(string $internalType): string;
}
