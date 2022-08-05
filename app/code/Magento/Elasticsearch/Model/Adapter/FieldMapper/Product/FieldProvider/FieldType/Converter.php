<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType;

/**
 * Field type converter from internal data types to elastic service.
 */
class Converter implements ConverterInterface
{
    /**#@+
     * Text flags for Elasticsearch field types
     */
    private const ES_DATA_TYPE_STRING = 'string';
    private const ES_DATA_TYPE_DOUBLE = 'double';
    private const ES_DATA_TYPE_INT = 'integer';
    private const ES_DATA_TYPE_DATE = 'date';
    /**#@-*/

    /**
     * Mapping between internal data types and elastic service.
     *
     * @var array
     */
    private $mapping = [
        self::INTERNAL_DATA_TYPE_STRING => self::ES_DATA_TYPE_STRING,
        self::INTERNAL_DATA_TYPE_KEYWORD => self::ES_DATA_TYPE_STRING,
        self::INTERNAL_DATA_TYPE_FLOAT => self::ES_DATA_TYPE_DOUBLE,
        self::INTERNAL_DATA_TYPE_INT => self::ES_DATA_TYPE_INT,
        self::INTERNAL_DATA_TYPE_DATE => self::ES_DATA_TYPE_DATE,
    ];

    /**
     * Get service field type for elasticsearch 2.
     *
     * @param string $internalType
     * @return string
     * @throws \DomainException
     */
    public function convert(string $internalType): string
    {
        if (!isset($this->mapping[$internalType])) {
            throw new \DomainException(sprintf('Unsupported internal field type: %s', $internalType));
        }
        return $this->mapping[$internalType];
    }
}
