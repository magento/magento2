<?php
/**
 * Converter of customer address format configuration from \DOMDocument to array
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address\Config;

/**
 * Class \Magento\Customer\Model\Address\Config\Converter
 *
 * @since 2.0.0
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert customer address format configuration from dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @since 2.0.0
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $formats */
        $formats = $source->getElementsByTagName('format');
        /** @var \DOMNode $formatConfig */
        foreach ($formats as $formatConfig) {
            $formatCode = $formatConfig->attributes->getNamedItem('code')->nodeValue;
            $output[$formatCode] = [];
            for ($attributeIndex = 0; $attributeIndex < $formatConfig->attributes->length; $attributeIndex++) {
                $output[$formatCode][$formatConfig->attributes->item(
                    $attributeIndex
                )->nodeName] = $formatConfig->attributes->item(
                    $attributeIndex
                )->nodeValue;
            }
        }
        return $output;
    }
}
