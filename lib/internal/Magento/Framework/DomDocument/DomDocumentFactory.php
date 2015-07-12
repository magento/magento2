<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DomDocument;

/**
 * DOM document factory
 */
class DomDocumentFactory
{
    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     */
    public function create()
    {
        return new \DOMDocument();
    }
}
