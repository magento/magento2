<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * {@inheritdoc}
     */
    public function convert(string $internalType)
    {
        return $this->mapping[$internalType];
    }
}
