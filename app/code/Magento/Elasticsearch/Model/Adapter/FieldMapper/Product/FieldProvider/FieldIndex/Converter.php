<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

/**
 * Field type converter from internal index type to elastic service.
 */
class Converter implements ConverterInterface
{
    /**
     * Text flags for Elasticsearch index value
     */
    private const ES_NO_INDEX = 'no';

    /**
     * Mapping between internal data types and elastic service.
     *
     * @var array
     */
    private $mapping = [
        'no_index' => self::ES_NO_INDEX,
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
