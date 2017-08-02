<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

/**
 * Interface DomMergerInterface
 * @since 2.0.0
 */
interface DomMergerInterface
{
    /**
     * Merge $xml into DOM document
     *
     * @param string $xml
     * @return void
     * @since 2.0.0
     */
    public function merge($xml);

    /**
     * Recursive merging of the \DOMElement into the original document
     *
     * Algorithm:
     * 1. Find the same node in original document
     * 2. Extend and override original document node attributes and scalar value if found
     * 3. Append new node if original document doesn't have the same node
     *
     * @param \DOMElement $node
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @since 2.0.0
     */
    public function mergeNode(\DOMElement $node);

    /**
     * Get DOM document
     *
     * @return \DOMDocument
     * @since 2.0.0
     */
    public function getDom();

    /**
     * Set DOM document
     *
     * @param \DOMDocument $domDocument
     * @return void
     * @since 2.0.0
     */
    public function setDom(\DOMDocument $domDocument);

    /**
     * Unset DOM document
     *
     * @return void
     * @since 2.0.0
     */
    public function unsetDom();

    /**
     * Validate self contents towards to specified schema
     *
     * @param string $schemaFileName
     * @return array
     * @since 2.0.0
     */
    public function validate($schemaFileName);
}
