<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DomDocument;

/**
 * DOM document factory
 * @since 2.0.0
 */
class DomDocumentFactory
{
    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     * @since 2.0.0
     */
    public function create()
    {
        return new \DOMDocument();
    }
}
