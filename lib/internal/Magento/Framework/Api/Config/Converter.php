<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Config;

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
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        /** @var \DOMNodeList $types */
        $types = $source->getElementsByTagName('custom_attributes');
        /** @var \DOMNode $type */
        foreach ($types as $type) {
            $typeConfig = [];
            $typeName = $type->getAttribute('for');

            $attributes = $type->getElementsByTagName('attribute');
            foreach ($attributes as $attribute) {
                $code = $attribute->getAttribute('code');
                $codeType = $attribute->getAttribute('type');

                if ($code && $codeType) {
                    $typeConfig[$code] = $codeType;
                }
            }

            $output[$typeName] = $typeConfig;
        }
        return $output;
    }
}
