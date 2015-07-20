<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Config;

/**
 * Converts publishers from \DOMDocument to array
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
        $output = [];

        /** @var $publisherNode \DOMNode */
        foreach ($source->getElementsByTagName('publisher') as $publisherNode) {
            $publisherName = $publisherNode->attributes->getNamedItem('name')->nodeValue;
            $data = [];
            $data['name'] = $publisherName;
            $data['connection'] = $publisherNode->attributes->getNamedItem('connection')->nodeValue;
            $data['exchange'] = $publisherNode->attributes->getNamedItem('exchange')->nodeValue;
            $output[$publisherName] = $data;
        }
        return $output;
    }
}
