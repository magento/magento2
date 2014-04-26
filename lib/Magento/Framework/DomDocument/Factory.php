<?php
/**
 * DOM document factory.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\DomDocument;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     */
    public function createDomDocument()
    {
        return $this->_objectManager->create('DOMDocument', array());
    }
}
