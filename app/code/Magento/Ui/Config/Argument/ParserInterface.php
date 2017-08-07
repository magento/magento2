<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Argument;

/**
 * Convert node to array
 * @since 2.2.0
 */
interface ParserInterface
{
    /**
     * Parse xml node to array
     *
     * @param array $data
     * @param \DOMNode $node
     * @return array
     * @since 2.2.0
     */
    public function parse(array $data, \DOMNode $node);
}
