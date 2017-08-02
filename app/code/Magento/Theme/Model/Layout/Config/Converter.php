<?php
/**
 * Page layout Config Converter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Config;

/**
 * Class \Magento\Theme\Model\Layout\Config\Converter
 *
 * @since 2.0.0
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convert($source)
    {
        $pageLayouts = [];
        $xpath = new \DOMXPath($source);

        /** @var $layout DOMNode */
        foreach ($xpath->query('/page_layouts/layout') as $layout) {
            $layoutAttributes = $layout->attributes;
            $id = $layoutAttributes->getNamedItem('id')->nodeValue;
            $pageLayouts[$id]['code'] = $id;

            /** @var $layoutSubNode DOMNode */
            foreach ($layout->childNodes as $layoutSubNode) {
                switch ($layoutSubNode->nodeName) {
                    case 'label':
                        $pageLayouts[$id][$layoutSubNode->nodeName] = $layoutSubNode->nodeValue;
                        break;
                    default:
                        break;
                }
            }
        }
        return $pageLayouts;
    }
}
