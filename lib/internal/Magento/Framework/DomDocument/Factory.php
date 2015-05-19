<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DomDocument;

/**
 * DOM document factory
 */
class Factory
{
    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     */
    public function createDomDocument()
    {
        return new \DOMDocument();
    }
}
