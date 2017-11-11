<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DomDocument;

use DOMDocument;

/**
 * DOM document factory
 */
class DomDocumentFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * DomDocumentFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create empty DOM document instance.
     *
     * @param string $data the data to be loaded into the object
     *
     * @return DOMDocument
     */
    public function create(string $data = null)
    {
        /** @var DOMDocument $dom */
        $dom = $this->objectManager->create(DOMDocument::class);

        if (!empty($data)) {
            $dom->loadXML($data);
        }

        return $dom;
    }
}
