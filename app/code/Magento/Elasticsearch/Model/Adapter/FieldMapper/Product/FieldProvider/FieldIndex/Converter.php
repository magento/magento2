<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

/**
 * Field type converter from internal index type to elastic service.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class Converter implements ConverterInterface
{
    /**
     * Text flags for Elasticsearch index value
     */
    private const ES_NO_INDEX = 'no';

    /**
     * Text flags for Elasticsearch no analyze index value
     */
    private const ES_NO_ANALYZE = 'not_analyzed';

    /**
     * Mapping between internal data types and elastic service.
     *
     * @var array
     */
    private $mapping = [
        ConverterInterface::INTERNAL_NO_INDEX_VALUE => self::ES_NO_INDEX,
        ConverterInterface::INTERNAL_NO_ANALYZE_VALUE => self::ES_NO_ANALYZE,
    ];

    /**
     * Get service field index type for elasticsearch 2.
     *
     * @param string $internalType
     * @return string|boolean
     * @throws \DomainException
     */
    public function convert(string $internalType)
    {
        if (!isset($this->mapping[$internalType])) {
            throw new \DomainException(sprintf('Unsupported internal field index type: %s', $internalType));
        }
        return $this->mapping[$internalType];
    }
}
