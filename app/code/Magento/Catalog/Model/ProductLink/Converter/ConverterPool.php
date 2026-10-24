<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\ProductLink\Converter;

/**
 * Return converter by link type
 */
class ConverterPool
{
    /**
     * @var ConverterInterface[]
     */
    protected $converters;

    /**
     * @var string
     */
    protected $defaultConverterCode = 'default';

    /**
     * @param  ConverterInterface[] $converters
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
     */
    public function getConverter($linkType)
    {
        return $this->converters[$linkType] ?? $this->converters[$this->defaultConverterCode];
    }
}
