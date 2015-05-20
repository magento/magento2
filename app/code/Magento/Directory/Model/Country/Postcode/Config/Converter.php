<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMNode $zipNode */
        foreach ($source->documentElement->childNodes as $zipNode) {
            if ($zipNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $groupName = $zipNode->attributes->getNamedItem('countryCode')->nodeValue;
            /** @var \DOMNode $codesNode */
            foreach ($zipNode->childNodes as $codesNode) {
                if ($codesNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                /** @var \DOMNode $code */
                foreach ($codesNode->childNodes as $code) {
                    if ($code->nodeType != XML_ELEMENT_NODE
                        || $code->attributes->getNamedItem('active')->nodeValue == 'false'
                    ) {
                        continue;
                    }
                    $result[$groupName][$code->attributes->getNamedItem('id')->nodeValue] = $code->nodeValue;
                }
            }
        }
        return $result;
    }
}
