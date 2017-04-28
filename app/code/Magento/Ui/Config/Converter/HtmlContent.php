<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Converter;

use Magento\Framework\View\Element\UiComponent\Config\Converter;
use Magento\Ui\Config\ConverterInterface;

/**
 * Converter for htmlContent component wrapped block
 */
class HtmlContent implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert(\DOMNode $node, array $data)
    {
        $items = [];
        /** @var \DOMElement $node */
        if ($node->nodeType == XML_ELEMENT_NODE) {
            $xml = '<?xml version="1.0"?>' . "\n"
                . '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . "\n"
                . $node->ownerDocument->saveXml($node) . "\n"
                . '</layout>';
            $items['layout']['xsi:type'] = 'string';
            $items['layout']['name'] = 'layout';
            $items['layout']['value'] = $xml;

            $items['name']['xsi:type'] = 'string';
            $items['name']['name'] = 'block';
            $items['name']['value'] = $node->getAttribute('name');

        }
        return [
            'xsi:type' => 'array',
            'item' => $items
        ];
    }
}
