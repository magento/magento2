<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];

        /** @var $publisherNode \DOMNode */
        foreach ($source->getElementsByTagName('publisher') as $publisherNode) {
            $publisherName = $this->_getAttributeValue($publisherNode, 'name');
            $data = [];
            $data['name'] = $publisherName;
            $data['connection'] = $this->_getAttributeValue($publisherNode, 'connection');
            $data['exchange'] = $this->_getAttributeValue($publisherNode, 'exchange');
            $output[$publisherName] = $data;
        }
        return $output;
    }
}
