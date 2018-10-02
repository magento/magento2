<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * Mapping between internal data types and elastic service.
     *
     * @var array
     */
    private $mapping = [
        'no_index' => self::ES_NO_INDEX,
    ];

    /**
     * Get service field index type for elasticsearch 5.
     *
     * @param string $internalType
     * @return string|boolean
     * @throws LocalizedException
     */
    public function convert(string $internalType)
    {
        if (!isset($this->mapping[$internalType])) {
            throw new LocalizedException(__('Unsupported internal field index type: %1', $internalType));
        }
        return $this->mapping[$internalType];
    }
}
