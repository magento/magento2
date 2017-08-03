<?php
/**
 * Converter of resources configuration from \DOMDocument to array
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection\Config;

/**
 * Class \Magento\Framework\App\ResourceConnection\Config\Converter
 *
 * @since 2.0.0
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     * @since 2.0.0
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
