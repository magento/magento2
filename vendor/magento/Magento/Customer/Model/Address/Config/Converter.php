<?php
/**
 * Converter of customer address format configuration from \DOMDocument to array
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Address\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert customer address format configuration from dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
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
