<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\GraphQl\Config\Common\Converter\XmlConverter;
use Magento\Framework\GraphQl\Config\Converter\NormalizerInterface;

/**
 * Convert data read from configuration sources into a format readable by GraphQL schema implementations
 */
class Converter implements ConverterInterface
{
    /**
     * @var XmlConverter
     */
    private $xmlConverter;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @param XmlConverter $xmlConverter
     * @param NormalizerInterface $normalizer
     */
    public function __construct(XmlConverter $xmlConverter, NormalizerInterface $normalizer)
    {
        $this->xmlConverter = $xmlConverter;
        $this->normalizer = $normalizer;
    }

    /**
     * Converts XML document into a formatted array mirroring the XML structure.
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        return $this->normalizer->normalize($this->xmlConverter->convert($source));
    }
}
