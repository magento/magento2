<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Converter\Dom;

/**
 * Converter of XML data to an array representation with no data loss excluding argument translation.
 */
class DiFlat extends Flat
{
    /**
     * Retrieve key-value pairs of node attributes excluding translate attribute.
     *
     * @param \DOMNode $node
     * @return array
     */
    protected function getNodeAttributes(\DOMNode $node)
    {
        $result = parent::getNodeAttributes($node);
        unset($result['translate']);

        return $result;
    }
}
