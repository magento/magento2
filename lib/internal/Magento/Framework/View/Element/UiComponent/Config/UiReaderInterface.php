<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

use Magento\Framework\Config\ReaderInterface;

/**
 * Interface UiReaderInterface
 *
 * @api
 */
interface UiReaderInterface extends ReaderInterface
{
    /**
     * Add xml content in the merged file
     *
     * @param string $xmlContent
     * @return void
     */
    public function addXMLContent($xmlContent);

    /**
     * Get content from the merged files
     *
     * @return string
     */
    public function getContent();

    /**
     * Get DOM document
     *
     * @return \DOMDocument
     */
    public function getDOMDocument();

    /**
     * Add DOM node into DOM document
     *
     * @param \DOMNode $node
     * @return void
     */
    public function addNode(\DOMNode $node);
}
