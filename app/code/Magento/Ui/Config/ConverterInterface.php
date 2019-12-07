<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config;

/**
 * Converter interface is used to convert UI Component XML configuration into UI Component interfaces arguments
 */
interface ConverterInterface
{
    /**
     * Convert DOMNode with specific converter to array according to data
     *
     * @param \DOMNode $node
     * @param array $data
     * @return array
     */
    public function convert(\DOMNode $node, array $data);
}
