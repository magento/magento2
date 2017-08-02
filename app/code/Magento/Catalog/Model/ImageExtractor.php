<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Helper\Image;

/**
 * Class \Magento\Catalog\Model\ImageExtractor
 *
 * @since 2.0.0
 */
class ImageExtractor implements \Magento\Framework\View\Xsd\Media\TypeDataExtractorInterface
{
    /**
     * Extract configuration data of images from the DOM structure
     *
     * @param \DOMElement $mediaNode
     * @param string $mediaParentTag
     * @return array
     * @since 2.0.0
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
                if ($attribute->tagName == 'background') {
                    $nodeValue = $this->processImageBackground($attribute->nodeValue);
                } else {
                    $nodeValue = $attribute->nodeValue;
                }
                $result[$mediaParentTag][$moduleNameImage][Image::MEDIA_TYPE_CONFIG_NODE][$imageId][$attribute->tagName]
                    = $nodeValue;
            }
        }

        return $result;
    }

    /**
     * Convert rgb background string into array
     *
     * @param string $backgroundString
     * @return int[]
     * @since 2.1.0
     */
    private function processImageBackground($backgroundString)
    {
        $pattern = '#\[(\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\]#';
        $backgroundArray = [];
        if (preg_match($pattern, $backgroundString, $backgroundArray)) {
            array_shift($backgroundArray);
        }
        return $backgroundArray;
    }
}
