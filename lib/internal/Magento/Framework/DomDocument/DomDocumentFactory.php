<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DomDocument;

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
     * @return \DOMDocument
     */
    public function create($data = null)
    {
        $dom = $this->objectManager->create('DOMDocument');

        if (!empty($data) && is_string($data)) {
            $dom->loadXML($data);
        }

        return $dom;
    }
}
