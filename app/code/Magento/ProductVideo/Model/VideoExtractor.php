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
     * @param \DOMElement $childNode
     * @return array
     */
    public function process(\DOMElement $childNode)
    {
        $result = [];
        $moduleName = $childNode->getAttribute('module');
        /** @var \DOMElement $node */
        foreach ($childNode->getElementsByTagName('video') as $node) {
            $videoId = $node->getAttribute('id');
            $result[$childNode->tagName][$moduleName][$videoId]['type'] = $node->getAttribute('type');
            foreach ($node->childNodes as $attribute) {
                if ($attribute->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $nodeValue = $attribute->nodeValue;
                $result[$childNode->tagName][$moduleName][$videoId][$attribute->tagName] = $nodeValue;
            }
        }
        return $result;
    }
}
