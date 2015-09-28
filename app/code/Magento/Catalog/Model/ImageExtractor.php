<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;


class ImageExtractor implements \Magento\Framework\Config\Reader\Xsd\Media\TypeDataExtractorInterface
{
    /**
     * Extract configuration data of images from the DOM structure
     *
     * @param \DOMElement $childNode
     * @return array
     */
    public function process(\DOMElement $childNode)
    {
        $result = [];
        $moduleName = $childNode->getAttribute('module');
        /** @var \DOMElement $node */
        foreach ($childNode->getElementsByTagName('image') as $node) {
            $imageId = $node->getAttribute('id');
            $result[$childNode->tagName][$moduleName][$imageId]['type'] = $node->getAttribute('type');
            foreach ($node->childNodes as $attribute) {
                if ($attribute->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $nodeValue = $attribute->nodeValue;
                $result[$childNode->tagName][$moduleName][$imageId][$attribute->tagName] = $nodeValue;
            }
        }
        return $result;
    }
}
