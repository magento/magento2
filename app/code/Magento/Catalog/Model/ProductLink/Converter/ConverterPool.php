<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink\Converter;

/**
 * Class \Magento\Catalog\Model\ProductLink\Converter\ConverterPool
 *
 * @since 2.0.0
 */
class ConverterPool
{
    /**
     * @var ConverterInterface[]
     * @since 2.0.0
     */
    protected $converters;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $defaultConverterCode = 'default';

    /**
     * @param  ConverterInterface[] $converters
     * @since 2.0.0
     */
    public function __construct(array $converters)
    {
        $this->converters = $converters;
    }

    /**
     * Get converter by link type
     *
     * @param string $linkType
     * @return ConverterInterface
     * @since 2.0.0
     */
    public function getConverter($linkType)
    {
        return isset($this->converters[$linkType])
            ? $this->converters[$linkType]
            : $this->converters[$this->defaultConverterCode];
    }
}
