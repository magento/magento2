<?php
/**
 * Page layout Config Converter
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
