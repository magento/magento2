<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Helper\Image;

class ImageExtractor implements \Magento\Framework\View\Xsd\Media\TypeDataExtractorInterface
{
    /**
     * Extract configuration data of images from the DOM structure
     *
     * @param \DOMElement $mediaNode
     * @param string $mediaParentTag
     * @return array
     */
    public function process(\DOMElement $mediaNode, $mediaParentTag)
    {
        $result = [];
        /** @var \DOMElement $node */
        $moduleNameImage = $mediaNode->getAttribute('module');
        foreach ($mediaNode->getElementsByTagName(ImageEntryConverter::MEDIA_TYPE_CODE) as $node) {
            $imageId = $node->getAttribute('id');
            $result[$mediaParentTag][$moduleNameImage][Image::MEDIA_TYPE_CONFIG_NODE][$imageId]['type']
                = $node->getAttribute('type');
            foreach ($node->childNodes as $attribute) {
                if ($attribute->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $nodeValue = $attribute->nodeValue;
                $result[$mediaParentTag][$moduleNameImage][Image::MEDIA_TYPE_CONFIG_NODE][$imageId][$attribute->tagName]
                    = $nodeValue;
            }
        }

        return $result;
    }
}
