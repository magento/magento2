<?php
/**
 * Converter of resources configuration from \DOMDocument to array
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $resources */
        $resources = $source->getElementsByTagName('resource');
        /** @var \DOMNode $resourceConfig */
        foreach ($resources as $resourceConfig) {
            $resourceName = $resourceConfig->attributes->getNamedItem('name')->nodeValue;
            $resourceData = [];
            foreach ($resourceConfig->attributes as $attribute) {
                $resourceData[$attribute->nodeName] = $attribute->nodeValue;
            }
            $output[$resourceName] = $resourceData;
        }
        return $output;
    }
}
