<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model;

class VideoExtractor implements \Magento\Framework\Config\Reader\Xsd\Media\TypeDataExtractorInterface
{
    /**
     * Extract configuration data of videos from the DOM structure
     *
     * @param \DOMElement $mediaNode
     * @param $mediaParentTag
     * @return array
     */
    public function process(\DOMElement $mediaNode, $mediaParentTag)
    {
        $result = [];
        $moduleNameVideo = $mediaNode->getAttribute('module');
        foreach ($mediaNode->getElementsByTagName('video') as $node) {
            $videoId = $node->getAttribute('id');
            $result[$mediaParentTag][$moduleNameVideo]['videos'][$videoId]['type']
                = $node->getAttribute('type');
            foreach ($node->childNodes as $attribute) {
                if ($attribute->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $nodeValue = $attribute->nodeValue;
                $result[$mediaParentTag][$moduleNameVideo]['videos'][$videoId][$attribute->tagName]
                    = $nodeValue;
            }
        }
        return $result;
    }
}
