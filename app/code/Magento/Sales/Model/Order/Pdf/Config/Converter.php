<?php
/**
 * Converter of pdf configuration from \DOMDocument to array
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf\Config;

/**
 * Class \Magento\Sales\Model\Order\Pdf\Config\Converter
 *
 */
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
        $result = ['renderers' => [], 'totals' => []];

        $pageTypes = $source->getElementsByTagName('page');
        foreach ($pageTypes as $pageType) {
            /** @var \DOMNode $pageType */
            $pageTypeName = $pageType->attributes->getNamedItem('type')->nodeValue;
            foreach ($pageType->childNodes as $rendererNode) {
                /** @var \DOMNode $rendererNode */
                if ($rendererNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $productType = $rendererNode->attributes->getNamedItem('product_type')->nodeValue;
                $result['renderers'][$pageTypeName][$productType] = $rendererNode->nodeValue;
            }
        }

        $totalItems = $source->getElementsByTagName('total');
        foreach ($totalItems as $item) {
            /** @var \DOMNode $item */
            $itemName = $item->attributes->getNamedItem('name')->nodeValue;
            foreach ($item->childNodes as $setting) {
                /** @var \DOMNode $setting */
                if ($setting->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $result['totals'][$itemName][$setting->nodeName] = $setting->nodeValue;
            }
        }

        return $result;
    }
}
