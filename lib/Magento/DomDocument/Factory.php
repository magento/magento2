<?php
/**
 * DOM document factory.
 *
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DomDocument;

class Factory
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
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
