<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\ElasticAdapter\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface;

/**
 * Field type converter from internal index type to elastic service.
 */
class Converter implements ConverterInterface
{
    /**
     * Text flags for Elasticsearch index value
     */
    private const ES_NO_INDEX = false;

    /**
     * Text flags for Elasticsearch no analyze index value
     */
    private const ES_NO_ANALYZE = false;

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
     * Get service field index type for elasticsearch 7.x and 8.x.
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
