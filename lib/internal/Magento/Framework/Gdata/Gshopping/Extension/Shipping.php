<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Extension for <scp:shipping> element
 *
 */
namespace Magento\Framework\Gdata\Gshopping\Extension;

class Shipping extends \Zend_Gdata_App_Extension_Element
{
    /**
     * Root namespace alias
     *
     * @var string
     */
    protected $_rootNamespace = 'scp';

    /**
     * Key-value pair of shipping info
     *
     * @var array
     */
    protected $_shippingInfo;

    /**
     * Creates instance of class
     *
     * @param array $shippingInfo as described in product requirements
     * @see http://code.google.com/intl/ru/apis/shopping/content/getting-started/requirements-products.html#shipping
     */
    public function __construct(array $shippingInfo = [])
    {
        $this->registerAllNamespaces(\Magento\Framework\Gdata\Gshopping\Content::$namespaces);
        parent::__construct('shipping', $this->_rootNamespace, $this->lookupNamespace($this->_rootNamespace));
        $this->_shippingInfo = $shippingInfo;
        foreach ($shippingInfo as $key => $value) {
            $this->_extensionElements[] = new \Zend_Gdata_App_Extension_Element(
                $key,
                $this->_rootNamespace,
                $this->_rootNamespaceURI,
                $value
            );
        }
    }

    /**
     * Magic getter to add access to _shippingInfo data
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->_shippingInfo[$name]) ? $this->_shippingInfo[$name] : parent::__get($name);
    }

    /**
     * Given a child \DOMNode, tries to determine how to map the data into
     * object instance members.  If no mapping is defined, Extension_Element
     * objects are created and stored in an array.
     *
     * @param \DOMNode $child The \DOMNode needed to be handled
     * @return void
     */
    protected function takeChildFromDOM($child)
    {
        if ($child->nodeType == XML_ELEMENT_NODE) {
            $this->_shippingInfo[$child->localName] = $child->textContent;
        }
        parent::takeChildFromDOM($child);
    }
}
