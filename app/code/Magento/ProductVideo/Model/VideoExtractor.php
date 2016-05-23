<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model;

use Magento\ProductVideo\Helper\Media;

class VideoExtractor implements \Magento\Framework\View\Xsd\Media\TypeDataExtractorInterface
{
    /**
     * Media Entry type code
     */
    const MEDIA_TYPE_CODE = 'video';

    /**
     * Extract configuration data of videos from the DOM structure
     *
     * @param \DOMElement $mediaNode
     * @param string $mediaParentTag
     * @return array
     */
    public function process(\DOMElement $mediaNode, $mediaParentTag)
    {
        $result = [];
        $moduleNameVideo = $mediaNode->getAttribute('module');
        foreach ($mediaNode->getElementsByTagName(self::MEDIA_TYPE_CODE) as $node) {
            $videoId = $node->getAttribute('id');
            $result[$mediaParentTag][$moduleNameVideo][Media::MEDIA_TYPE_CONFIG_NODE][$videoId]['type']
                = $node->getAttribute('type');
            foreach ($node->childNodes as $attribute) {
                if ($attribute->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $nodeValue = $attribute->nodeValue;
                $result[$mediaParentTag][$moduleNameVideo][Media::MEDIA_TYPE_CONFIG_NODE][$videoId][$attribute->tagName]
                    = $nodeValue;
            }
        }
        return $result;
    }
}
