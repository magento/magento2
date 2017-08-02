<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode\Config;

/**
 * Class \Magento\Directory\Model\Country\Postcode\Config\Converter
 *
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var \Magento\Framework\Stdlib\BooleanUtils
     */
    protected $booleanUtils;

    /**
     * @param \Magento\Framework\Stdlib\BooleanUtils $booleanUtils
     */
    public function __construct(\Magento\Framework\Stdlib\BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

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
                        || !$this->booleanUtils->toBoolean($code->attributes->getNamedItem('active')->nodeValue)
                    ) {
                        continue;
                    }
                    $result[$groupName][$code->attributes->getNamedItem('id')->nodeValue] = [
                        'example' => $code->attributes->getNamedItem('example')->nodeValue,
                        'pattern' => $code->nodeValue
                    ];
                }
            }
        }
        return $result;
    }
}
