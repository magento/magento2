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
     * @param \DOMElement $mediaNode
     * @param $mediaParentTag
     * @return array
     */
    public function process(\DOMElement $mediaNode, $mediaParentTag)
    {
        $result = [];
        /** @var \DOMElement $node */
        $moduleNameImage = $mediaNode->getAttribute('module');
        foreach ($mediaNode->getElementsByTagName('image') as $node) {
            $imageId = $node->getAttribute('id');
            $result[$mediaParentTag][$moduleNameImage]['images'][$imageId]['type']
                = $node->getAttribute('type');
            foreach ($node->childNodes as $attribute) {
                if ($attribute->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $nodeValue = $attribute->nodeValue;
                $result[$mediaParentTag][$moduleNameImage]['images'][$imageId][$attribute->tagName]
                    = $nodeValue;
            }
        }

        return $result;
    }
}
