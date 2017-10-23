<?php
/**
 * Page layout Config Converter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $pageLayouts = [];
        $xpath = new \DOMXPath($source);

        /** @var $layout DOMNode */
        foreach ($xpath->query('/page_layouts/layout') as $layout) {
            /** @noinspection PhpUndefinedFieldInspection */
            $layoutAttributes = $layout->attributes;
            $id = $layoutAttributes->getNamedItem('id')->nodeValue;
            $pageLayouts[$id]['code'] = $id;

            /** @var $layoutSubNode DOMNode */
            /** @noinspection PhpUndefinedFieldInspection */
            foreach ($layout->childNodes as $layoutSubNode) {
                /** @noinspection PhpUndefinedFieldInspection */
                switch ($layoutSubNode->nodeName) {
                    case 'label':
                        /** @noinspection PhpUndefinedFieldInspection */
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
