<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\PageType\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $pageTypes = [];
        $xpath = new \DOMXPath($source);

        /** @var $widget \DOMNode */
        foreach ($xpath->query('/page_types/type') as $type) {
            $typeAttributes = $type->attributes;

            $id = $typeAttributes->getNamedItem('id')->nodeValue;
            $label = $typeAttributes->getNamedItem('label')->nodeValue;

            $pageArray = ["id" => $id, "label" => $label];

            $pageTypes[$id] = $pageArray;
        }
        return $pageTypes;
    }
}
